<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasRole('Admin');
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasRole('Admin');
    }

    public function restore(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasRole('Admin');
    }

    public function forceDelete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasRole('Admin');
    }
}
