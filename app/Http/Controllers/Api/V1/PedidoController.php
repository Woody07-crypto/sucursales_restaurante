<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoEstadoRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Pedido::class);

        $query = Pedido::query()->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado'));
        }

        if ($request->filled('canal')) {
            $query->where('canal', $request->string('canal'));
        }

        if ($request->filled('sucursal')) {
            $query->where('sucursal', $request->string('sucursal'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->string('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->string('hasta'));
        }

        return PedidoResource::collection($query->paginate(15));
    }

    public function store(StorePedidoRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $payload = $request->validated();
        $total = collect($payload['items'])->sum(
            fn (array $item) => ((int) $item['cantidad']) * ((float) $item['precio_unitario'])
        );

        $pedido = Pedido::create([
            'codigo' => $this->generateCodigo(),
            'cliente_nombre' => $payload['cliente_nombre'] ?? null,
            'canal' => $payload['canal'],
            'sucursal' => $payload['sucursal'],
            'estado' => 'pendiente',
            'total' => $total,
            'items' => $payload['items'],
            'notas' => $payload['notas'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return (new PedidoResource($pedido))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Pedido $pedido): PedidoResource
    {
        $this->authorize('view', $pedido);

        return new PedidoResource($pedido);
    }

    public function updateEstado(UpdatePedidoEstadoRequest $request, Pedido $pedido): JsonResponse
    {
        $this->authorize('updateEstado', $pedido);

        $nuevoEstado = $request->string('estado')->toString();

        if (! $this->canTransition($pedido->estado, $nuevoEstado)) {
            return response()->json([
                'message' => "No se puede cambiar de estado '{$pedido->estado}' a '{$nuevoEstado}'.",
            ], 422);
        }

        $pedido->update(['estado' => $nuevoEstado]);

        return (new PedidoResource($pedido->fresh()))
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Pedido $pedido): JsonResponse
    {
        $this->authorize('delete', $pedido);

        if ($pedido->estado === 'entregado') {
            return response()->json([
                'message' => 'No se puede eliminar un pedido entregado.',
            ], 409);
        }

        $pedido->delete();

        return response()->json([], 204);
    }

    private function generateCodigo(): string
    {
        return 'PED-'.now()->format('Ymd-His').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
    }

    private function canTransition(string $actual, string $nuevo): bool
    {
        $transiciones = [
            'pendiente' => ['en_preparacion', 'cancelado'],
            'en_preparacion' => ['listo', 'cancelado'],
            'listo' => ['entregado', 'cancelado'],
            'entregado' => [],
            'cancelado' => [],
        ];

        return in_array($nuevo, $transiciones[$actual] ?? [], true);
    }
}
