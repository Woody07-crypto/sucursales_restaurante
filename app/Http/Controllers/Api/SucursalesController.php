<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSucursalRequest;
use App\Http\Requests\UpdateSucursalRequest;
use App\Models\Sucursal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SucursalesController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'flow' => 'sucursales',
            'message' => 'Servicio de sucursales operativo',
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Sucursal::query()->orderBy('nombre');

        if ($request->has('activa')) {
            $activa = filter_var($request->query('activa'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($activa !== null) {
                $query->where('activa', $activa);
            }
        }

        if ($request->boolean('paginate')) {
            $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
            $paginator = $query->paginate($perPage);

            return response()->json([
                'data' => collect($paginator->items())->map(fn (Sucursal $s) => $this->serializeSucursal($s))->values(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ]);
        }

        $items = $query->get()->map(fn (Sucursal $s) => $this->serializeSucursal($s));

        return response()->json(['data' => $items]);
    }

    public function store(StoreSucursalRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('activa', $data)) {
            $data['activa'] = true;
        }

        $sucursal = Sucursal::query()->create($data);

        return response()->json(['data' => $this->serializeSucursal($sucursal)], 201);
    }

    public function show(Sucursal $sucursal): JsonResponse
    {
        return response()->json(['data' => $this->serializeSucursal($sucursal)]);
    }

    public function update(UpdateSucursalRequest $request, Sucursal $sucursal): JsonResponse
    {
        $sucursal->update($request->validated());

        return response()->json(['data' => $this->serializeSucursal($sucursal->fresh())]);
    }

    public function destroy(Sucursal $sucursal): JsonResponse
    {
        $sucursal->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSucursal(Sucursal $s): array
    {
        return [
            'id' => $s->id,
            'nombre' => $s->nombre,
            'direccion' => $s->direccion,
            'ciudad' => $s->ciudad,
            'telefono' => $s->telefono,
            'email' => $s->email,
            'horario' => $s->horario,
            'activa' => $s->activa,
            'created_at' => $s->created_at?->toIso8601String(),
            'updated_at' => $s->updated_at?->toIso8601String(),
        ];
    }
}
