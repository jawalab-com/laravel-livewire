<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Users')] class extends Component {
    use WithPagination;

    public string $search = '';

    #[Locked]
    public ?int $deletingUserId = null;
    public string $deletingUserName = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    /**
     * Reset pagination when searching.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Get paginated users with roles eager-loaded.
     *
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::with('roles')
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * Prompt delete confirmation modal for a user.
     */
    public function confirmDelete(int $userId): void
    {
        $user = User::findOrFail($userId);
        Gate::authorize('delete', $user);

        $this->deletingUserId = $user->id;
        $this->deletingUserName = $user->name;

        $this->dispatch('open-modal', name: 'delete-user-modal');
    }

    /**
     * Delete the target user.
     */
    public function deleteUser(): void
    {
        if (! $this->deletingUserId) {
            return;
        }

        $user = User::findOrFail($this->deletingUserId);
        Gate::authorize('delete', $user);

        $user->delete();

        $this->dispatch('close-modal', name: 'delete-user-modal');
        $this->reset(['deletingUserId', 'deletingUserName']);

        Flux::toast(variant: 'success', text: __('User deleted successfully.'));
    }
}; ?>

<div class="w-full max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Users') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage system user accounts, credentials, and access roles.') }}</flux:subheading>
        </div>

        @can('create', App\Models\User::class)
            <flux:button variant="primary" icon="plus" :href="route('users.create')" wire:navigate data-test="create-user-button">
                {{ __('New user') }}
            </flux:button>
        @endcan
    </div>

    <div class="flex items-center justify-between gap-4">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search users by name or email...') }}"
            icon="magnifying-glass"
            size="sm"
            class="max-w-sm"
            data-test="search-users-input"
        />
    </div>

    <flux:separator variant="subtle" />

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 overflow-hidden shadow-xs">
        @forelse ($this->users as $user)
            <div
                wire:key="user-{{ $user->id }}"
                class="flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-5 gap-4 {{ ! $loop->last ? 'border-b border-zinc-200 dark:border-zinc-700' : '' }}"
                data-test="user-row-{{ $user->id }}"
            >
                <div class="flex items-center gap-3.5">
                    <flux:avatar
                        :name="$user->name"
                        :initials="$user->initials()"
                        class="size-10 shrink-0"
                    />

                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</span>
                            @if ($user->id === auth()->id())
                                <flux:badge size="sm" color="zinc">{{ __('You') }}</flux:badge>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ $user->email }}</span>
                            <span class="opacity-40">•</span>
                            @if ($user->roles->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->roles as $role)
                                        <flux:badge size="sm" :color="$role->name === 'Super Admin' ? 'amber' : 'zinc'">
                                            {{ $role->name }}
                                        </flux:badge>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500 italic">{{ __('No role') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:self-center">
                    @can('update', $user)
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="pencil-square"
                            :href="route('users.edit', $user)"
                            wire:navigate
                            data-test="edit-user-button-{{ $user->id }}"
                        >
                            {{ __('Edit') }}
                        </flux:button>
                    @endcan

                    @can('delete', $user)
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="trash"
                            wire:click="confirmDelete({{ $user->id }})"
                            class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50"
                            data-test="delete-user-button-{{ $user->id }}"
                        >
                            {{ __('Delete') }}
                        </flux:button>
                    @endcan
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                    <flux:icon.users class="size-6" />
                </div>
                <p class="font-medium">{{ __('No users found') }}</p>
                <flux:text variant="subtle" class="mt-1 text-sm">
                    {{ $search ? __('No users matched your search criteria.') : __('Get started by creating a new user.') }}
                </flux:text>
            </div>
        @endforelse
    </div>

    @if ($this->users->hasPages())
        <div>
            {{ $this->users->links() }}
        </div>
    @endif

    {{-- Delete User Modal --}}
    <flux:modal name="delete-user-modal" class="md:w-[420px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete user') }}</flux:heading>
                <flux:subheading>
                    {{ __('Are you sure you want to delete ":name"? Their account access will be permanently removed.', ['name' => $deletingUserName]) }}
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="deleteUser">
                    {{ __('Delete user') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
