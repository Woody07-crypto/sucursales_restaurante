<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AlertaStock;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockAnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pedido::class);

        $q = AlertaStock::query()->with('ingrediente:id,nombre')->latest();

        if ($request->filled('sucursal')) {
            $q->where('sucursal', $request->string('sucursal'));
        }

        if ($request->filled('nivel')) {
            $q->where('nivel', $request->string('nivel'));
        }

        return response()->json($q->paginate(20));
    }
}
