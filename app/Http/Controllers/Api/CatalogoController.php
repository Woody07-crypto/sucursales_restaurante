<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'flow' => 'catalogo',
            'message' => 'Flujo catálogo operativo',
        ]);
    }

    public function menu(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $query = Product::query()
            ->where('activo', true)
            ->orderBy('nombre');

        if ($user->isGerenteGlobal()) {
            return response()->json($query->get());
        }

        if ($user->isGerenteSucursal() || $user->role === 'cajero') {
            if (! $user->sucursal_id) {
                abort(403, 'Usuario sin sucursal asignada');
            }
            $query->where('sucursal_id', $user->sucursal_id);

            return response()->json($query->get());
        }

        abort(403, 'No autorizado para el menú');
    }
}
