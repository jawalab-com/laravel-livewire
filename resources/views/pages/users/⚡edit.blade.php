<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Title('Edit User')] class extends Component {
    public User $user;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public array $selectedRoles = [];

    /**
     * Mount the component.
     */
    public function mount(User $user): void
    {
        Gate::authorize('update', $user);

        $this->user = $user->load('roles');
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    /**
     * Get all available roles.
     *
     * @return Collection<int, Role>
     */
    #[Computed]
    public function availableRoles(): Collection
    {
        return Role::withCount('permissions')->orderBy('name')->get();
    }

    /**
     * Update the user.
     */
    public function save(): void
    {
        Gate::authorize('update', $this->user);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['string', 'exists:roles,name'],
        ];

        if (! empty($this->password)) {
            $rules['password'] = ['required', 'string', Password::default()];
        }

        $validated = $this->validate($rules);

        DB::transaction(function () use ($validated) {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $updateData['password'] = $validated['password'];
            }

            $this->user->update($updateData);

            $this->user->syncRoles($validated['selectedRoles'] ?? []);
        });

        Flux::toast(variant: 'success', text: __('User updated successfully.'));

        $this->redirectRoute('users.index', navigate: true);
    }
}; ?>

<div class="w-full max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-2">
        <flux:button variant="ghost" icon="arrow-left" size="sm" :href="route('users.index')" wire:navigate aria-label="{{ __('Back to users') }}" />
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl" level="1">{{ __('Edit User') }}: {{ $this->user->name }}</flux:heading>
                @if ($this->user->id === auth()->id())
                    <flux:badge size="sm" color="zinc">{{ __('You') }}</flux:badge>
                @endif
            </div>
            <flux:subheading size="lg">{{ __('Update credentials and modify system role assignments.') }}</flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    @include('pages.users.partials.form', [
        'submitLabel' => __('Save changes'),
        'isEditing' => true,
    ])
</div>
