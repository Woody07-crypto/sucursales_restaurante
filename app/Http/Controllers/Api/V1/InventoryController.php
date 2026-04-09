<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\StockSucursalIngrediente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function byBranch(Request $request, string $sucursal): JsonResponse
    {
        $this->authorize('viewAny', Pedido::class);

        $sucursal = urldecode($sucursal);

        $rows = StockSucursalIngrediente::query()
            ->where('sucursal', $sucursal)
            ->with('ingrediente:id,nombre,slug,umbral')
            ->orderBy('ingrediente_id')
            ->get()
            ->map(fn (StockSucursalIngrediente $r) => [
                'ingrediente_id' => $r->ingrediente_id,
                'nombre' => $r->ingrediente?->nombre,
                'slug' => $r->ingrediente?->slug,
                'cantidad' => (float) $r->cantidad,
                'umbral' => $r->ingrediente ? (float) $r->ingrediente->umbral : null,
                'bajo_umbral' => $r->ingrediente && (float) $r->cantidad < (float) $r->ingrediente->umbral,
            ]);

        return response()->json([
            'sucursal' => $sucursal,
            'items' => $rows,
        ]);
    }
}
