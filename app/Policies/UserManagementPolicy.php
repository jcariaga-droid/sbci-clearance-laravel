<?php

namespace App\Policies;

use App\Models\User;

class UserManagementPolicy
{
    public function canDelete(User $actor, User $target): bool
    {
        if ($actor->role !== 'admin' || (string) $actor->getKey() === (string) $target->getKey()) {
            return false;
        }

        if (in_array($target->role, ['student', 'staff'], true)) {
            return true;
        }

        return $target->role === 'admin' && $actor->isMainAdmin();
    }

    public function canChangeRole(User $actor, User $target, string $newRole): bool
    {
        if (
            $actor->role !== 'admin'
            || !in_array($newRole, ['student', 'staff', 'admin'], true)
            || (string) $actor->getKey() === (string) $target->getKey()
            || $target->isMainAdmin()
        ) {
            return false;
        }

        $roleChangeInvolvesAdmin = $target->role === 'admin' || $newRole === 'admin';

        return !$roleChangeInvolvesAdmin || $actor->isMainAdmin();
    }
}
