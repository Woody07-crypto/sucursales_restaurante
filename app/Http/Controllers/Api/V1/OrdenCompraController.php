<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OrdenCompra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdenCompraController extends Controller
{
    /**
     * POST /api/v1/orders/purchase — orden de compra sugerida (manual o integración).
     */
    public function suggested(Request $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $data = $request->validate([
            'sucursal' => ['required', 'string', 'max:120'],
            'ingrediente_id' => ['required', 'integer', 'exists:ingredientes,id'],
            'cantidad_sugerida' => ['required', 'numeric', 'min:0.001'],
            'pedido_id' => ['nullable', 'integer', 'exists:pedidos,id'],
        ]);

        $oc = OrdenCompra::create([
            'sucursal' => $data['sucursal'],
            'ingrediente_id' => $data['ingrediente_id'],
            'tipo' => 'sugerida',
            'cantidad_sugerida' => $data['cantidad_sugerida'],
            'estado' => 'pendiente',
            'pedido_id' => $data['pedido_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Orden de compra sugerida registrada.',
            'orden_compra' => [
                'id' => $oc->id,
                'tipo' => $oc->tipo,
                'cantidad_sugerida' => (float) $oc->cantidad_sugerida,
            ],
        ], 201);
    }

    /**
     * POST /api/v1/purchase-orders — orden de compra urgente.
     */
    public function urgent(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['gerente', 'admin'], true), 403);

        $data = $request->validate([
            'sucursal' => ['required', 'string', 'max:120'],
            'ingrediente_id' => ['required', 'integer', 'exists:ingredientes,id'],
            'cantidad_sugerida' => ['required', 'numeric', 'min:0.001'],
            'pedido_id' => ['nullable', 'integer', 'exists:pedidos,id'],
        ]);

        $oc = OrdenCompra::create([
            'sucursal' => $data['sucursal'],
            'ingrediente_id' => $data['ingrediente_id'],
            'tipo' => 'urgente',
            'cantidad_sugerida' => $data['cantidad_sugerida'],
            'estado' => 'pendiente',
            'pedido_id' => $data['pedido_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Orden de compra urgente registrada.',
            'orden_compra' => [
                'id' => $oc->id,
                'tipo' => $oc->tipo,
                'cantidad_sugerida' => (float) $oc->cantidad_sugerida,
            ],
        ], 201);
    }
}
