<?php

use App\Actions\Teams\AssignToPublicTeam;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Title('Create User')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public array $selectedRoles = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('create', User::class);
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
     * Save the new user.
     */
    public function save(AssignToPublicTeam $assignToPublicTeam): void
    {
        Gate::authorize('create', User::class);

        $validated = $this->validate([
            ...$this->profileRules(),
            'password' => ['required', 'string', Password::default()],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['string', 'exists:roles,name'],
        ]);

        DB::transaction(function () use ($validated, $assignToPublicTeam) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $assignToPublicTeam->handle($user);

            if (! empty($validated['selectedRoles'])) {
                $user->syncRoles($validated['selectedRoles']);
            }
        });

        Flux::toast(variant: 'success', text: __('User created successfully.'));

        $this->redirectRoute('users.index', navigate: true);
    }
}; ?>

<div class="w-full max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-2">
        <flux:button variant="ghost" icon="arrow-left" size="sm" :href="route('users.index')" wire:navigate aria-label="{{ __('Back to users') }}" />
        <div>
            <flux:heading size="xl" level="1">{{ __('Create User') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Add a new user account and configure their assigned roles.') }}</flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    @include('pages.users.partials.form', [
        'submitLabel' => __('Create user'),
        'isEditing' => false,
    ])
</div>
