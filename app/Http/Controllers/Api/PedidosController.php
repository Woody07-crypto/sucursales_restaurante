<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoEstadoRequest;
use App\Models\Pedido;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $query = Pedido::query()->orderByDesc('id');

        if ($user->isGerenteGlobal()) {
            return $this->respond($query->get(), 'Listado de pedidos');
        }

        if ($user->isGerenteSucursal() || $user->role === 'cajero') {
            if (! $user->sucursal_id) {
                abort(403, 'Usuario sin sucursal asignada');
            }
            $query->where('sucursal_id', $user->sucursal_id);

            return $this->respond($query->get(), 'Listado de pedidos');
        }

        abort(403);
    }

    public function show(Request $request, int $pedido): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $model = Pedido::query()->find($pedido);
        if (! $model) {
            abort(404);
        }

        if ($user->isGerenteGlobal()) {
            return $this->respond($model, 'Detalle de pedido');
        }

        if ($user->isGerenteSucursal() || $user->role === 'cajero') {
            if (! $user->sucursal_id || (int) $user->sucursal_id !== (int) $model->sucursal_id) {
                abort(404);
            }

            return $this->respond($model, 'Detalle de pedido');
        }

        abort(403);
    }

    public function updateEstado(UpdatePedidoEstadoRequest $request, int $pedido): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $model = Pedido::query()->find($pedido);
        if (! $model) {
            abort(404);
        }

        if ($user->isGerenteGlobal()) {
            $model->estado = $request->validated('estado');
            $model->save();

            return $this->respond(['id' => $model->id, 'estado' => $model->estado], 'Estado actualizado');
        }

        if ($user->isGerenteSucursal()) {
            if (! $user->sucursal_id || (int) $user->sucursal_id !== (int) $model->sucursal_id) {
                abort(404);
            }
            $model->estado = $request->validated('estado');
            $model->save();

            return $this->respond(['id' => $model->id, 'estado' => $model->estado], 'Estado actualizado');
        }

        abort(403);
    }

    public function destroy(Request $request, int $pedido): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $model = Pedido::query()->find($pedido);
        if (! $model) {
            abort(404);
        }

        if ($user->isGerenteGlobal()) {
            $model->delete();

            return $this->respond(null, 'Pedido eliminado');
        }

        abort(403);
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
            $qty = (int) $line['cantidad'];

            // Ruta A (matriz): product_id => calculamos desde Product.
            if (! empty($line['product_id'])) {
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

                $unit = (float) $product->precio;
                $sub = $unit * $qty;
                $total += $sub;
                $lines[] = [
                    'product_id' => $product->id,
                    'nombre' => $product->nombre,
                    'cantidad' => $qty,
                    'precio_unitario' => $unit,
                    'subtotal' => $sub,
                ];
                continue;
            }

            // Ruta B (PDF ejemplo): nombre + precio_unitario => guardamos línea directa.
            if (empty($line['nombre']) || ! array_key_exists('precio_unitario', $line)) {
                throw ValidationException::withMessages([
                    'items' => ['Cada item debe incluir product_id o (nombre y precio_unitario).'],
                ]);
            }

            $unit = (float) $line['precio_unitario'];
            $sub = $unit * $qty;
            $total += $sub;
            $lines[] = [
                'product_id' => null,
                'nombre' => (string) $line['nombre'],
                'cantidad' => $qty,
                'precio_unitario' => $unit,
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

        return $this->respond(
            ['id' => $pedido->id, 'estado' => $pedido->estado],
            'Pedido registrado correctamente',
            201
        );
    }
}
