<?php

use App\Enums\Permission as AppPermission;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Title('Edit Role')] class extends Component {
    public Role $role;
    public string $name = '';
    public array $permissions = [];

    /**
     * Mount the component.
     */
    public function mount(Role $role): void
    {
        Gate::authorize('update', $role);

        $this->role = $role->load('permissions');
        $this->name = $role->name;
        $this->permissions = $role->permissions->pluck('name')->toArray();
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
     * Update the existing role.
     */
    public function save(): void
    {
        Gate::authorize('update', $this->role);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->role->id),
            ],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(AppPermission::values())],
        ], [
            'name.required' => __('Role name is required.'),
            'name.unique' => __('A role with this name already exists.'),
        ]);

        $this->role->update(['name' => $validated['name']]);
        $this->role->syncPermissions($validated['permissions'] ?? []);

        Flux::toast(variant: 'success', text: __('Role updated successfully.'));

        $this->redirectRoute('roles.index', navigate: true);
    }
}; ?>

<div class="w-full max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-2">
        <flux:button variant="ghost" icon="arrow-left" size="sm" :href="route('roles.index')" wire:navigate aria-label="{{ __('Back to roles') }}" />
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl" level="1">{{ __('Edit Role') }}: {{ $this->role->name }}</flux:heading>
                @if ($this->role->name === 'Super Admin')
                    <flux:badge size="sm" color="amber">{{ __('System') }}</flux:badge>
                @endif
            </div>
            <flux:subheading size="lg">{{ __('Modify role name and adjust assigned permissions.') }}</flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    @include('pages.roles.partials.form', ['submitLabel' => __('Save changes')])
</div>
