<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use App\Models\Pedido;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PedidosController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'flow' => 'pedidos',
            'message' => 'Flujo pedidos operativo',
        ]);
    }

    public function store(StorePedidoRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $sucursal = Sucursal::query()->where('nombre', $request->validated('sucursal'))->first();
        if (! $sucursal) {
            throw ValidationException::withMessages([
                'sucursal' => ['No se encontró la sucursal indicada.'],
            ]);
        }

        if ($user->isGerenteSucursal()) {
            if (! $user->sucursal_id || (int) $user->sucursal_id !== (int) $sucursal->id) {
                abort(403, 'No puede crear pedidos para otra sucursal');
            }
        } elseif (! $user->isGerenteGlobal() && $user->role !== 'cajero') {
            abort(403);
        }

        if ($user->role === 'cajero' && $user->sucursal_id && (int) $user->sucursal_id !== (int) $sucursal->id) {
            abort(403, 'El pedido debe corresponder a su sucursal');
        }

        $itemsIn = $request->validated('items');
        $lines = [];
        $total = 0.0;

        foreach ($itemsIn as $line) {
            $product = Product::query()
                ->whereKey($line['product_id'])
                ->where('sucursal_id', $sucursal->id)
                ->where('activo', true)
                ->first();

            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => ['Producto inválido o inactivo para esta sucursal.'],
                ]);
            }

            $qty = (int) $line['cantidad'];
            $sub = (float) $product->precio * $qty;
            $total += $sub;
            $lines[] = [
                'product_id' => $product->id,
                'nombre' => $product->nombre,
                'cantidad' => $qty,
                'precio_unitario' => (float) $product->precio,
                'subtotal' => $sub,
            ];
        }

        $pedido = Pedido::query()->create([
            'sucursal_id' => $sucursal->id,
            'codigo' => strtoupper(Str::random(10)),
            'cliente_nombre' => $request->validated('cliente_nombre'),
            'canal' => $request->validated('canal'),
            'sucursal' => $sucursal->nombre,
            'estado' => 'pendiente',
            'total' => $total,
            'items' => $lines,
            'notas' => $request->validated('notas'),
            'created_by' => $user->id,
        ]);

        return response()->json($pedido, 201);
    }
}
