<?php

namespace App\Policies;

use App\Models\MasterLapor;
use App\Models\User;

class MasterLaporPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('master_lapor.view') || $user->hasPermission('master_lapor.manage');
    }

    public function view(User $user, MasterLapor $masterLapor): bool
    {
        return $user->hasPermission('master_lapor.view') || $user->hasPermission('master_lapor.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('master_lapor.manage');
    }

    public function update(User $user, MasterLapor $masterLapor): bool
    {
        return $user->hasPermission('master_lapor.manage');
    }

    public function delete(User $user, MasterLapor $masterLapor): bool
    {
        return $user->hasPermission('master_lapor.manage');
    }

    public function restore(User $user, MasterLapor $masterLapor): bool
    {
        return $user->hasPermission('master_lapor.manage');
    }

    public function forceDelete(User $user, MasterLapor $masterLapor): bool
    {
        return $user->hasPermission('master_lapor.manage');
    }
}
