<?php

namespace App\Policies;

use App\Models\Position;
use App\Models\User;

class PositionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function view(User $user, Position $position): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(User $user, Position $position): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function delete(User $user, Position $position): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Position $position): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Position $position): bool
    {
        return $user->isSuperAdmin();
    }
}
