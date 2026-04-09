<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSucursalRequest;
use App\Http\Requests\UpdateSucursalRequest;
use App\Models\AuditLog;
use App\Models\Pedido;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        /** @var User $user */
        $user = $request->user();

        $query = Sucursal::query()->orderBy('nombre');

        if ($user->isGerenteSucursal()) {
            $query->whereKey($user->sucursal_id);
        }

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

        if (Sucursal::query()->where('nombre', $data['nombre'])->exists()) {
            return response()->json([
                'message' => 'Ya existe una sucursal con ese nombre.',
            ], 409);
        }

        if (! array_key_exists('activa', $data)) {
            $data['activa'] = true;
        }

        $sucursal = Sucursal::query()->create($data);

        return response()->json(['data' => $this->serializeSucursal($sucursal)], 201);
    }

    public function show(Request $request, Sucursal $sucursal): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->canAccessSucursal($sucursal), 403);

        return response()->json([
            'data' => $this->serializeSucursal($sucursal, includeKpis: true),
        ]);
    }

    public function update(UpdateSucursalRequest $request, Sucursal $sucursal): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['nombre']) && $data['nombre'] !== $sucursal->nombre) {
            if (Sucursal::query()->where('nombre', $data['nombre'])->whereKeyNot($sucursal->id)->exists()) {
                return response()->json([
                    'message' => 'Ya existe una sucursal con ese nombre.',
                ], 409);
            }
        }

        $sucursal->update($data);

        $this->writeAudit($request->user(), $sucursal, 'updated');

        return response()->json(['data' => $this->serializeSucursal($sucursal->fresh())]);
    }

    public function destroy(Request $request, Sucursal $sucursal): JsonResponse|Response
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->canAccessSucursal($sucursal), 403);

        if ($sucursal->hasPedidosActivos()) {
            return response()->json([
                'message' => 'No se puede eliminar la sucursal mientras tenga pedidos activos.',
            ], 409);
        }

        $this->writeAudit($user, $sucursal, 'deleted');
        $sucursal->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSucursal(Sucursal $s, bool $includeKpis = false): array
    {
        $base = [
            'id' => $s->id,
            'nombre' => $s->nombre,
            'direccion' => $s->direccion,
            'ciudad' => $s->ciudad,
            'telefono' => $s->telefono,
            'email' => $s->email,
            'horario' => $s->horario,
            'activa' => $s->activa,
            'manager_id' => $s->manager_id,
            'created_at' => $s->created_at?->toIso8601String(),
            'updated_at' => $s->updated_at?->toIso8601String(),
        ];

        if ($includeKpis) {
            $base['kpis'] = $this->kpis($s);
        }

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    private function kpis(Sucursal $sucursal): array
    {
        $base = Pedido::query()->where('sucursal_id', $sucursal->id);

        return [
            'total_pedidos' => (clone $base)->count(),
            'pedidos_activos' => (clone $base)->whereIn('estado', Pedido::ESTADOS_ACTIVOS)->count(),
            'total_ventas' => (string) (clone $base)->sum('total'),
            'stock_resumen' => null,
        ];
    }

    private function writeAudit(?User $user, Sucursal $sucursal, string $action): void
    {
        AuditLog::query()->create([
            'user_id' => $user?->id,
            'auditable_type' => Sucursal::class,
            'auditable_id' => $sucursal->id,
            'action' => $action,
        ]);
    }
}
