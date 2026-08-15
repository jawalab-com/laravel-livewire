@props([
    'submitLabel' => __('Save changes'),
])

<form wire:submit="save" class="space-y-6">
    <flux:field>
        <flux:label>{{ __('Role Name') }}</flux:label>
        <flux:input wire:model="name" placeholder="{{ __('e.g. Content Manager') }}" required autofocus />
        <flux:error name="name" />
    </flux:field>

    <div class="space-y-4">
        <flux:label class="font-medium">{{ __('Permissions') }}</flux:label>

        @foreach ($this->groupedPermissions as $group => $permissions)
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 p-5 space-y-4">
                <div>
                    <flux:heading size="sm">{{ __($group) }}</flux:heading>
                    <flux:text variant="subtle" class="text-xs">{{ __('Configure permissions related to :group', ['group' => strtolower($group)]) }}</flux:text>
                </div>

                <flux:separator variant="subtle" />

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <flux:checkbox
                            wire:model="permissions"
                            value="{{ $permission->value }}"
                            :label="$permission->label()"
                            :description="$permission->description()"
                        />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center gap-3 pt-2">
        <flux:button variant="primary" type="submit">
            {{ $submitLabel }}
        </flux:button>

        <flux:button variant="filled" :href="route('roles.index')" wire:navigate>
            {{ __('Cancel') }}
        </flux:button>
    </div>
</form>
