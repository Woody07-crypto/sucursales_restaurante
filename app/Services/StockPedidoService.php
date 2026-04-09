<?php

namespace App\Services;

use App\Models\AlertaStock;
use App\Models\Ingrediente;
use App\Models\InventarioMovimiento;
use App\Models\MenuItemBloqueado;
use App\Models\OrdenCompra;
use App\Models\Pedido;
use App\Models\ProductoIngrediente;
use App\Models\StockSucursalIngrediente;
use Illuminate\Support\Facades\DB;

class StockPedidoService
{
    /**
     * Flujograma 3: al registrar pedido → movimientos, alertas, órdenes de compra, bloqueo de menú.
     *
     * @return array{http_status: int, payload: array<string, mixed>}
     */
    public function procesarTrasPedido(Pedido $pedido): array
    {
        $sucursal = $pedido->sucursal;
        $items = $pedido->items ?? [];
        $afectados = [];

        DB::transaction(function () use ($pedido, $sucursal, $items, &$afectados) {
            foreach ($items as $line) {
                $nombreProducto = mb_strtolower(trim((string) ($line['nombre'] ?? '')));
                if ($nombreProducto === '') {
                    continue;
                }

                $recetas = ProductoIngrediente::query()
                    ->whereRaw('LOWER(TRIM(producto_nombre)) = ?', [$nombreProducto])
                    ->get();

                $cantidadLinea = (int) ($line['cantidad'] ?? 0);
                if ($cantidadLinea < 1) {
                    continue;
                }

                foreach ($recetas as $receta) {
                    $consumo = (float) $receta->cantidad_por_unidad * $cantidadLinea;
                    if ($consumo <= 0) {
                        continue;
                    }

                    $stock = StockSucursalIngrediente::query()->firstOrCreate(
                        ['sucursal' => $sucursal, 'ingrediente_id' => $receta->ingrediente_id],
                        ['cantidad' => 0]
                    );

                    $nueva = max(0, (float) $stock->cantidad - $consumo);
                    $stock->update(['cantidad' => $nueva]);

                    InventarioMovimiento::create([
                        'sucursal' => $sucursal,
                        'ingrediente_id' => $receta->ingrediente_id,
                        'cantidad_delta' => -$consumo,
                        'tipo' => 'salida_pedido',
                        'pedido_id' => $pedido->id,
                    ]);

                    $afectados[$receta->ingrediente_id] = true;
                }
            }

            foreach (array_keys($afectados) as $ingredienteId) {
                $this->evaluarUmbralYAlertas($pedido, $sucursal, (int) $ingredienteId);
            }
        });

        return $this->construirRespuesta($pedido);
    }

    private function evaluarUmbralYAlertas(Pedido $pedido, string $sucursal, int $ingredienteId): void
    {
        $ing = Ingrediente::query()->find($ingredienteId);
        if (! $ing) {
            return;
        }

        $stock = StockSucursalIngrediente::query()
            ->where('sucursal', $sucursal)
            ->where('ingrediente_id', $ingredienteId)
            ->first();

        $cantidad = $stock ? (float) $stock->cantidad : 0.0;
        $umbral = (float) $ing->umbral;

        if ($cantidad <= 0) {
            AlertaStock::create([
                'sucursal' => $sucursal,
                'ingrediente_id' => $ingredienteId,
                'nivel' => 'critical',
                'mensaje' => "Stock crítico: {$ing->nombre} agotado en sucursal {$sucursal}. Ítems del menú asociados bloqueados.",
                'pedido_id' => $pedido->id,
            ]);

            $this->bloquearProductosPorIngrediente($pedido, $sucursal, $ingredienteId);

            OrdenCompra::create([
                'sucursal' => $sucursal,
                'ingrediente_id' => $ingredienteId,
                'tipo' => 'urgente',
                'cantidad_sugerida' => max($umbral * 3, 10),
                'estado' => 'pendiente',
                'pedido_id' => $pedido->id,
            ]);

            return;
        }

        if ($cantidad < $umbral) {
            AlertaStock::create([
                'sucursal' => $sucursal,
                'ingrediente_id' => $ingredienteId,
                'nivel' => 'warning',
                'mensaje' => "Stock bajo: {$ing->nombre} por debajo del umbral ({$umbral}) en {$sucursal}.",
                'pedido_id' => $pedido->id,
            ]);

            OrdenCompra::create([
                'sucursal' => $sucursal,
                'ingrediente_id' => $ingredienteId,
                'tipo' => 'sugerida',
                'cantidad_sugerida' => max($umbral * 2 - $cantidad, $umbral),
                'estado' => 'pendiente',
                'pedido_id' => $pedido->id,
            ]);
        }
    }

    private function bloquearProductosPorIngrediente(Pedido $pedido, string $sucursal, int $ingredienteId): void
    {
        $productos = ProductoIngrediente::query()
            ->where('ingrediente_id', $ingredienteId)
            ->pluck('producto_nombre')
            ->unique();

        foreach ($productos as $nombre) {
            MenuItemBloqueado::query()->updateOrCreate(
                [
                    'sucursal' => $sucursal,
                    'producto_nombre' => $nombre,
                ],
                ['ingrediente_id' => $ingredienteId, 'pedido_id' => $pedido->id]
            );
        }
    }

    private function construirRespuesta(Pedido $pedido): array
    {
        $alertas = AlertaStock::query()
            ->where('pedido_id', $pedido->id)
            ->with('ingrediente:id,nombre,slug')
            ->orderByDesc('id')
            ->get()
            ->map(fn (AlertaStock $a) => [
                'id' => $a->id,
                'nivel' => $a->nivel,
                'mensaje' => $a->mensaje,
                'ingrediente' => $a->ingrediente?->nombre,
            ])
            ->values()
            ->all();

        $ordenes = OrdenCompra::query()
            ->where('pedido_id', $pedido->id)
            ->with('ingrediente:id,nombre')
            ->orderByDesc('id')
            ->get()
            ->map(fn (OrdenCompra $o) => [
                'id' => $o->id,
                'tipo' => $o->tipo,
                'cantidad_sugerida' => (float) $o->cantidad_sugerida,
                'ingrediente' => $o->ingrediente?->nombre,
            ])
            ->values()
            ->all();

        $bloqueos = MenuItemBloqueado::query()
            ->where('pedido_id', $pedido->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (MenuItemBloqueado $m) => [
                'producto_nombre' => $m->producto_nombre,
                'ingrediente_id' => $m->ingrediente_id,
            ])
            ->values()
            ->all();

        $tieneCritical = collect($alertas)->contains(fn ($a) => $a['nivel'] === 'critical');
        $tieneWarning = collect($alertas)->contains(fn ($a) => $a['nivel'] === 'warning');

        $estado = 'ok';
        if ($tieneCritical) {
            $estado = 'critical';
        } elseif ($tieneWarning) {
            $estado = 'warning';
        }

        // Flujograma: 207 con alerta (warning); 201 pedido + crítica; sin alerta coherente con REST 201.
        $http = 201;
        if ($tieneWarning && ! $tieneCritical) {
            $http = 207;
        }

        $payload = [
            'estado' => $estado,
            'alertas' => $alertas,
            'ordenes_compra' => $ordenes,
            'items_menu_bloqueados' => $bloqueos,
            'notificaciones' => $this->notificacionesSimuladas($tieneCritical, $tieneWarning),
        ];

        return ['http_status' => $http, 'payload' => $payload];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function notificacionesSimuladas(bool $critical, bool $warning): array
    {
        $out = [];
        if ($critical) {
            $out[] = ['rol' => 'gerente_general', 'canal' => 'in_app', 'mensaje' => 'Alerta crítica de stock: revisar orden de compra urgente.'];
            $out[] = ['rol' => 'supervisor_sucursal', 'canal' => 'in_app', 'mensaje' => 'Stock crítico: ítems bloqueados en menú.'];
        } elseif ($warning) {
            $out[] = ['rol' => 'gerente', 'canal' => 'in_app', 'mensaje' => 'Stock bajo: revisar orden de compra sugerida.'];
        }

        return $out;
    }
}
