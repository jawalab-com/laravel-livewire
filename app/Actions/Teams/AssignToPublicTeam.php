<?php

namespace App\Actions\Teams;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AssignToPublicTeam
{
    /**
     * Assign the user to the shared "Public" team, creating it if needed.
     */
    public function handle(User $user): Team
    {
        return DB::transaction(function () use ($user) {
            $team = Team::where('slug', 'public')->first();

            if (! $team) {
                $team = $this->createPublicTeam();
            }

            if (! $user->belongsToTeam($team)) {
                $team->memberships()->create([
                    'user_id' => $user->id,
                    'role' => TeamRole::Member->value,
                ]);
            }

            $user->switchTeam($team);

            return $team;
        });
    }

    /**
     * Create the shared "Public" team with id 1 when available.
     */
    protected function createPublicTeam(): Team
    {
        try {
            return Team::forceCreate([
                'id' => 1,
                'name' => 'Public',
                'slug' => 'public',
                'is_personal' => false,
            ]);
        } catch (QueryException) {
            return Team::create([
                'name' => 'Public',
                'slug' => 'public',
                'is_personal' => false,
            ]);
        }
    }
}
