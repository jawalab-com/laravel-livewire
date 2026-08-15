<?php

use App\Enums\Permission as AppPermission;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Title('Roles & Permissions')] class extends Component {
    #[Locked]
    public ?int $deletingRoleId = null;
    public string $deletingRoleName = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', Role::class);
    }

    /**
     * Get all roles with permissions and user count.
     *
     * @return Collection<int, Role>
     */
    #[Computed]
    public function roles(): Collection
    {
        return Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();
    }

    /**
     * Prompt delete confirmation modal for a role.
     */
    public function confirmDelete(int $roleId): void
    {
        $role = Role::findOrFail($roleId);
        Gate::authorize('delete', $role);

        $this->deletingRoleId = $role->id;
        $this->deletingRoleName = $role->name;

        $this->dispatch('open-modal', name: 'delete-role-modal');
    }

    /**
     * Delete the role.
     */
    public function deleteRole(): void
    {
        if (! $this->deletingRoleId) {
            return;
        }

        $role = Role::findOrFail($this->deletingRoleId);
        Gate::authorize('delete', $role);

        $role->delete();

        $this->dispatch('close-modal', name: 'delete-role-modal');
        $this->reset(['deletingRoleId', 'deletingRoleName']);

        Flux::toast(variant: 'success', text: __('Role deleted successfully.'));
    }
}; ?>

<div class="w-full max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Roles & Permissions') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage system access levels, user roles, and fine-grained permissions.') }}</flux:subheading>
        </div>

        @can('create', Spatie\Permission\Models\Role::class)
            <flux:button variant="primary" icon="plus" :href="route('roles.create')" wire:navigate data-test="create-role-button">
                {{ __('New role') }}
            </flux:button>
        @endcan
    </div>

    <flux:separator variant="subtle" />

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 overflow-hidden shadow-xs">
        @forelse ($this->roles as $role)
            <div
                wire:key="role-{{ $role->id }}"
                class="flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-5 gap-4 {{ ! $loop->last ? 'border-b border-zinc-200 dark:border-zinc-700' : '' }}"
                data-test="role-row-{{ $role->id }}"
            >
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                        <flux:icon.shield-check class="size-5" />
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $role->name }}</span>
                            @if ($role->name === 'Super Admin')
                                <flux:badge size="sm" color="amber">{{ __('System') }}</flux:badge>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ trans_choice('{0} 0 permissions|{1} 1 permission|[2,*] :count permissions', $role->permissions->count(), ['count' => $role->permissions->count()]) }}</span>
                            <span class="opacity-40">•</span>
                            <span>{{ trans_choice('{0} 0 users|{1} 1 user|[2,*] :count users', $role->users_count, ['count' => $role->users_count]) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:self-center">
                    @can('update', $role)
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="pencil-square"
                            :href="route('roles.edit', $role)"
                            wire:navigate
                            data-test="edit-role-button-{{ $role->id }}"
                        >
                            {{ __('Edit') }}
                        </flux:button>
                    @endcan

                    @can('delete', $role)
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="trash"
                            wire:click="confirmDelete({{ $role->id }})"
                            class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50"
                            data-test="delete-role-button-{{ $role->id }}"
                        >
                            {{ __('Delete') }}
                        </flux:button>
                    @endcan
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                    <flux:icon.shield-check class="size-6" />
                </div>
                <p class="font-medium">{{ __('No roles configured') }}</p>
                <flux:text variant="subtle" class="mt-1 text-sm">{{ __('Create your first role to start assigning permissions.') }}</flux:text>
            </div>
        @endforelse
    </div>

    {{-- Delete Role Modal --}}
    <flux:modal name="delete-role-modal" class="md:w-[420px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete role') }}</flux:heading>
                <flux:subheading>
                    {{ __('Are you sure you want to delete the role ":name"? This action cannot be undone.', ['name' => $deletingRoleName]) }}
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="deleteRole">
                    {{ __('Delete role') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
