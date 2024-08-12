<?php

namespace App\Policies;

use App\Models\Loans;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LoansPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            'Super Admin',
            'Member'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Loans $loans): bool
    {
        return $user->hasRole([
            'Super Admin',
            'Member'
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole([
            'Super Admin',
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Loans $loans): bool
    {
        return $user->hasRole([
            'Super Admin',
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Loans $loans): bool
    {
        return $user->hasRole([
            'Super Admin',
        ]);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole([
            'Super Admin',
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, Loans $loans): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, Loans $loans): bool
    // {
    //     //
    // }
}
