<?php

use App\Enums\Permission as AppPermission;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Title('Create Role')] class extends Component {
    public string $name = '';
    public array $permissions = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('create', Role::class);
    }

    /**
     * Get all available permissions grouped.
     *
     * @return array<string, array<AppPermission>>
     */
    #[Computed]
    public function groupedPermissions(): array
    {
        $groups = [];
        foreach (AppPermission::cases() as $permission) {
            $groups[$permission->group()][] = $permission;
        }

        return $groups;
    }

    /**
     * Save the newly created role.
     */
    public function save(): void
    {
        Gate::authorize('create', Role::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(AppPermission::values())],
        ], [
            'name.required' => __('Role name is required.'),
            'name.unique' => __('A role with this name already exists.'),
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        Flux::toast(variant: 'success', text: __('Role created successfully.'));

        $this->redirectRoute('roles.index', navigate: true);
    }
}; ?>

<div class="w-full max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-2">
        <flux:button variant="ghost" icon="arrow-left" size="sm" :href="route('roles.index')" wire:navigate aria-label="{{ __('Back to roles') }}" />
        <div>
            <flux:heading size="xl" level="1">{{ __('Create Role') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Define a new user role and assign its system permissions.') }}</flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    @include('pages.roles.partials.form', ['submitLabel' => __('Create role')])
</div>
