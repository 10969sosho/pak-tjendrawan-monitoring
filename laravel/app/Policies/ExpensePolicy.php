<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Expense $expense): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('Admin'); }
    public function update(User $user, Expense $expense): bool { return $user->hasRole('Admin'); }
    public function delete(User $user, Expense $expense): bool { return $user->hasRole('Admin'); }
}
