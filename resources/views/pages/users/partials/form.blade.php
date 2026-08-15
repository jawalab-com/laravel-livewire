@props([
    'submitLabel' => __('Save changes'),
    'isEditing' => false,
])

<form wire:submit="save" class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 p-5 sm:p-6 space-y-5">
        <flux:heading size="md">{{ __('Account Details') }}</flux:heading>

        <div class="grid gap-5 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('Full Name') }}</flux:label>
                <flux:input wire:model="name" placeholder="{{ __('e.g. Alex Morgan') }}" required autofocus />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Email Address') }}</flux:label>
                <flux:input wire:model="email" type="email" placeholder="{{ __('alex@example.com') }}" required />
                <flux:error name="email" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>
                {{ __('Password') }}
                @if ($isEditing)
                    <span class="text-xs font-normal text-zinc-500">({{ __('Leave blank to keep current password') }})</span>
                @endif
            </flux:label>
            <flux:input wire:model="password" type="password" viewable :required="! $isEditing" />
            <flux:error name="password" />
        </flux:field>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 p-5 sm:p-6 space-y-4">
        <div>
            <flux:heading size="md">{{ __('Role Assignment') }}</flux:heading>
            <flux:text variant="subtle" class="text-xs">{{ __('Select the roles assigned to this user to control their access.') }}</flux:text>
        </div>

        <flux:separator variant="subtle" />

        <div class="grid gap-3 sm:grid-cols-2">
            @forelse ($this->availableRoles as $role)
                <flux:checkbox
                    wire:model="selectedRoles"
                    value="{{ $role->name }}"
                    :label="$role->name"
                    :description="trans_choice('{0} 0 permissions|{1} 1 permission|[2,*] :count permissions', $role->permissions_count ?? $role->permissions->count(), ['count' => $role->permissions_count ?? $role->permissions->count()])"
                />
            @empty
                <flux:text variant="subtle" class="text-xs sm:col-span-2">
                    {{ __('No custom roles found. Create roles in the Roles menu first.') }}
                </flux:text>
            @endforelse
        </div>
        <flux:error name="selectedRoles" />
    </div>

    <div class="flex items-center gap-3 pt-2">
        <flux:button variant="primary" type="submit">
            {{ $submitLabel }}
        </flux:button>

        <flux:button variant="filled" :href="route('users.index')" wire:navigate>
            {{ __('Cancel') }}
        </flux:button>
    </div>
</form>
