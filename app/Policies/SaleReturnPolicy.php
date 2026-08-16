<?php

namespace App\Policies;

use App\Models\SaleReturn;
use App\Models\User;

class SaleReturnPolicy
{
    /**
     * المدير يرى كل الإرجاعات، والبائع يرى إرجاعاته فقط.
     */
    public function view(User $user, SaleReturn $saleReturn): bool
    {
        return $user->isAdmin() || $saleReturn->user_id === $user->id;
    }

    public function delete(User $user, SaleReturn $saleReturn): bool
    {
        return $user->can('manage-returns')
            && ($user->isAdmin() || $saleReturn->user_id === $user->id);
    }
}
