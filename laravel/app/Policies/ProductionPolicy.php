<?php

namespace App\Policies;

use App\Models\Production;
use App\Models\User;

class ProductionPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Production $production): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('Admin'); }
    public function update(User $user, Production $production): bool { return $user->hasRole('Admin'); }
    public function delete(User $user, Production $production): bool { return $user->hasRole('Admin'); }
}
