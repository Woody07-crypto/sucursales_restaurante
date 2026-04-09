<?php

namespace App\Policies;

use App\Models\Pedido;
use App\Models\User;

class PedidoPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'gerente', 'cajero'], true);
    }

    public function view(User $user, Pedido $pedido): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'gerente', 'cajero'], true);
    }

    public function updateEstado(User $user, Pedido $pedido): bool
    {
        return in_array($user->role, ['admin', 'gerente'], true);
    }

    public function delete(User $user, Pedido $pedido): bool
    {
        return $user->role === 'admin';
    }
}
