<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSucursalRequest;
use App\Http\Requests\UpdateSucursalRequest;
use App\Models\AuditLog;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SucursalesController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'flow' => 'sucursales',
            'message' => 'Flujo sucursales operativo',
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireSucursalesReader($request);

        $query = Sucursal::query()->orderBy('nombre');

        if ($user->isGerenteSucursal()) {
            if (! $user->sucursal_id) {
                abort(403, 'Usuario sin sucursal asignada');
            }
            $query->whereKey($user->sucursal_id);
        }

        return $this->respond($query->get(), 'Listado de sucursales');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->requireSucursalesReader($request);

        $sucursal = Sucursal::query()->find($id);
        if (! $sucursal) {
            abort(404);
        }

        $this->ensureBranchScope($user, $sucursal);

        $kpis = [
            'pedidos_activos' => $sucursal->activePedidos()->count(),
            'pedidos_totales' => $sucursal->pedidos()->count(),
            'productos_activos' => $sucursal->products()->where('activo', true)->count(),
        ];

        return $this->respond(array_merge($sucursal->toArray(), ['kpis' => $kpis]), 'Detalle de sucursal');
    }

    public function store(StoreSucursalRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User || ! $user->isGerenteGlobal()) {
            abort(403, 'Solo el gerente global puede crear sucursales');
        }

        if (Sucursal::query()->where('nombre', $request->validated('nombre'))->exists()) {
            return $this->respond(null, 'Conflicto: ya existe una sucursal con ese nombre', 409);
        }

        $data = $request->validated();
        $sucursal = Sucursal::query()->create($data);

        return $this->respond($sucursal, 'Sucursal creada', 201);
    }

    public function update(UpdateSucursalRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $sucursal = Sucursal::query()->find($id);
        if (! $sucursal) {
            abort(404);
        }

        if ($user->isGerenteSucursal()) {
            if (! $user->sucursal_id || (int) $user->sucursal_id !== (int) $sucursal->id) {
                abort(404);
            }
        } elseif (! $user->isGerenteGlobal()) {
            abort(403);
        }

        $sucursal->fill($request->validated());
        $sucursal->save();

        AuditLog::query()->create([
            'user_id' => $user->id,
            'auditable_type' => Sucursal::class,
            'auditable_id' => $sucursal->id,
            'action' => 'sucursal.updated',
        ]);

        return $this->respond($sucursal, 'Sucursal actualizada');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $sucursal = Sucursal::query()->find($id);
        if (! $sucursal) {
            abort(404);
        }

        if ($user->isGerenteSucursal()) {
            if (! $user->sucursal_id || (int) $user->sucursal_id !== (int) $sucursal->id) {
                abort(403);
            }
        } elseif (! $user->isGerenteGlobal()) {
            abort(403);
        }

        if ($sucursal->activePedidos()->exists()) {
            return $this->respond(null, 'No se puede eliminar la sucursal: existen pedidos activos', 409);
        }

        $sucursal->delete();

        AuditLog::query()->create([
            'user_id' => $user->id,
            'auditable_type' => Sucursal::class,
            'auditable_id' => $sucursal->id,
            'action' => 'sucursal.soft_deleted',
        ]);

        return $this->respond(['id' => $sucursal->id], 'Sucursal eliminada (baja lógica)', 200);
    }

    private function requireSucursalesReader(Request $request): User
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        if ($user->isGerenteGlobal() || $user->isGerenteSucursal()) {
            return $user;
        }

        abort(403, 'No autorizado para consultar sucursales');
    }

    private function ensureBranchScope(User $user, Sucursal $sucursal): void
    {
        if ($user->isGerenteGlobal()) {
            return;
        }

        if ($user->isGerenteSucursal()) {
            if (! $user->sucursal_id || (int) $user->sucursal_id !== (int) $sucursal->id) {
                abort(403);
            }

            return;
        }

        abort(403);
    }
}
