<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * المدير يرى كل الفواتير، والبائع يرى فواتيره فقط.
     */
    public function view(User $user, Sale $sale): bool
    {
        return $user->isAdmin() || $sale->user_id === $user->id;
    }

    public function update(User $user, Sale $sale): bool
    {
        return $user->can('manage-sales')
            && ($user->isAdmin() || $sale->user_id === $user->id);
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->can('manage-sales')
            && ($user->isAdmin() || $sale->user_id === $user->id);
    }
}
