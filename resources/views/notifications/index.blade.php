<x-layouts::app :title="__('Notifications')">
    <div class="mx-auto flex h-full w-full max-w-3xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Notifications') }}</flux:heading>
            <flux:subheading>{{ $unreadCount }} {{ __('unread') }}</flux:subheading>
        </div>

        <div class="flex flex-col gap-3">
            @forelse ($notifications as $notification)
                <a
                    href="{{ route('notifications.show', $notification) }}"
                    class="flex items-start gap-3 rounded-xl border border-neutral-200 p-4 transition hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-700 dark:hover:border-neutral-600 dark:hover:bg-neutral-800"
                >
                    <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $notification->isUnread() ? 'bg-red-500' : 'bg-transparent' }}"></span>
                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-4">
                            <flux:heading class="text-sm">{{ $notification->title }}</flux:heading>
                            <flux:text variant="subtle" class="shrink-0 text-xs">{{ $notification->created_at->diffForHumans() }}</flux:text>
                        </div>
                        @if ($notification->body)
                            <flux:text class="mt-1 line-clamp-2 text-sm text-neutral-500 dark:text-neutral-400">{{ $notification->body }}</flux:text>
                        @endif
                    </div>
                </a>
            @empty
                <div class="rounded-xl border border-dashed border-neutral-300 p-10 text-center dark:border-neutral-700">
                    <flux:text variant="subtle">{{ __('No notifications yet.') }}</flux:text>
                </div>
            @endforelse
        </div>

        <div>
            {{ $notifications->links() }}
        </div>
    </div>
</x-layouts::app>
