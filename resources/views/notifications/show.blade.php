<x-layouts::app :title="$notification->title">
    <div class="mx-auto flex h-full w-full max-w-3xl flex-col gap-6">
        <div>
            <flux:button variant="ghost" icon="arrow-left" :href="route('notifications.index')">
                {{ __('Back to notifications') }}
            </flux:button>
        </div>

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex items-start gap-3">
                <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $notification->isUnread() ? 'bg-red-500' : 'bg-transparent' }}"></span>
                <div class="flex-1">
                    <div class="flex items-center justify-between gap-4">
                        <flux:heading class="text-sm">{{ $notification->title }}</flux:heading>
                        @if ($notification->isUnread())
                            <flux:badge color="blue" inset="top bottom">{{ __('Unread') }}</flux:badge>
                        @endif
                    </div>
                    <flux:text variant="subtle" class="text-xs">{{ $notification->created_at->diffForHumans() }}</flux:text>

                    @if ($notification->body)
                        <div class="mt-4 text-sm leading-relaxed text-neutral-600 dark:text-neutral-300">
                            {{ $notification->body }}
                        </div>
                    @endif

                    <div class="mt-6 flex items-center gap-2">
                        @if ($notification->isUnread())
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <flux:button variant="primary" type="submit" size="sm">{{ __('Mark as read') }}</flux:button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('notifications.unread', $notification) }}">
                                @csrf
                                <flux:button variant="subtle" type="submit" size="sm">{{ __('Mark as unread') }}</flux:button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
