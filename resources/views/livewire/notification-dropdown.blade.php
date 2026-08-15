<flux:dropdown position="bottom" align="end" class="ms-auto">
    <div class="relative">
        <flux:button variant="outline" icon="bell" size="sm" square data-test="notification-dropdown-trigger" />
        @if ($this->unreadCount() > 0)
            <span class="absolute -end-0.5 -top-0.5 size-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-zinc-900"></span>
        @endif
    </div>

    <flux:menu class="w-80">
        <div class="flex items-center justify-between px-2 pb-2 pt-1">
            <flux:heading class="text-sm font-semibold">{{ $this->unreadCount() }} {{ __('unread') }}</flux:heading>
        </div>

        <flux:menu.separator />

        @forelse ($this->notifications() as $notification)
            <flux:menu.item
                :href="route('notifications.show', $notification)"
                class="cursor-pointer"
                wire:navigate
            >
                <div class="flex w-full items-start gap-2">
                    <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $notification->isUnread() ? 'bg-red-500' : 'bg-transparent' }}"></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate text-sm font-medium">{{ $notification->title }}</span>
                            <span class="shrink-0 text-xs text-zinc-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        @if ($notification->body)
                            <span class="line-clamp-1 text-xs text-zinc-400">{{ $notification->body }}</span>
                        @endif
                    </div>
                </div>
            </flux:menu.item>
        @empty
            <div class="px-2 py-6 text-center">
                <flux:text variant="subtle" class="text-sm">{{ __('No notifications') }}</flux:text>
            </div>
        @endforelse

        <flux:menu.separator />

        <flux:menu.item :href="route('notifications.index')" icon-trailing="arrow-right" class="cursor-pointer" wire:navigate>
            {{ __('View all') }}
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
