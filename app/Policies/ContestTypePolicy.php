<?php

namespace App\Policies;

use App\Models\ContestType;
use App\Models\User;

class ContestTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function view(User $user, ContestType $contestType): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(User $user, ContestType $contestType): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function delete(User $user, ContestType $contestType): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, ContestType $contestType): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, ContestType $contestType): bool
    {
        return $user->isSuperAdmin();
    }
}
