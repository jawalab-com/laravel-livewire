<?php

namespace App\Policies;

use App\Enums\Permission as AppPermission;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any activities.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(AppPermission::ViewAuditLogs->value);
    }

    /**
     * Determine whether the user can view the activity.
     */
    public function view(User $user, Activity $activity): bool
    {
        return $user->can(AppPermission::ViewAuditLogs->value);
    }

    /**
     * Prevent manual deletion of audit records.
     */
    public function delete(User $user, Activity $activity): bool
    {
        return false;
    }
}
