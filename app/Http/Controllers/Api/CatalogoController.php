<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
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
            return $this->respond($query->get(), 'Menú de productos activos');
        }

        if ($user->isGerenteSucursal() || $user->role === 'cajero') {
            if (! $user->sucursal_id) {
                abort(403, 'Usuario sin sucursal asignada');
            }
            $query->where('sucursal_id', $user->sucursal_id);

            return $this->respond($query->get(), 'Menú de productos activos');
        }

        abort(403, 'No autorizado para el menú');
    }

    public function productsIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $query = Product::query()->orderBy('nombre');

        if ($user->isGerenteGlobal()) {
            return $this->respond($query->get(), 'Listado de productos');
        }

        if ($user->isGerenteSucursal() || $user->role === 'cajero') {
            if (! $user->sucursal_id) {
                abort(403, 'Usuario sin sucursal asignada');
            }
            $query->where('sucursal_id', $user->sucursal_id);

            return $this->respond($query->get(), 'Listado de productos');
        }

        abort(403, 'No autorizado para consultar productos');
    }

    public function productsStore(StoreProductRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        if (! $user->isGerenteGlobal()) {
            abort(403, 'Solo el gerente global puede crear productos');
        }

        $product = Product::query()->create($request->validated());

        return $this->respond($product, 'Producto creado', 201);
    }

    public function productsUpdate(UpdateProductRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $product = Product::query()->find($id);
        if (! $product) {
            abort(404);
        }

        if ($user->isGerenteGlobal()) {
            $product->fill($request->validated());
            $product->save();

            return $this->respond($product, 'Producto actualizado');
        }

        if ($user->isGerenteSucursal()) {
            if (! $user->sucursal_id || (int) $user->sucursal_id !== (int) $product->sucursal_id) {
                abort(404);
            }

            // Un gerente_sucursal no puede mover productos entre sucursales.
            $data = $request->safe()->except(['sucursal_id']);
            $product->fill($data);
            $product->save();

            return $this->respond($product, 'Producto actualizado');
        }

        abort(403, 'No autorizado para actualizar productos');
    }

    public function productsDestroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $product = Product::query()->find($id);
        if (! $product) {
            abort(404);
        }

        if ($user->isGerenteGlobal()) {
            $product->delete();

            return $this->respond(null, 'Producto eliminado');
        }

        if ($user->isGerenteSucursal()) {
            if (! $user->sucursal_id || (int) $user->sucursal_id !== (int) $product->sucursal_id) {
                abort(404);
            }

            $product->delete();

            return $this->respond(null, 'Producto eliminado');
        }

        abort(403, 'No autorizado para eliminar productos');
    }
}
