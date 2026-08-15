<?php

use App\Enums\TeamRole;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Developer - Notification')] class extends Component {
    public string $title = '';
    public string $body = '';
    public string $type = '';

    public function send(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:100'],
        ]);

        Notification::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'body' => $validated['body'] ?: null,
            'type' => $validated['type'] ?: null,
        ]);

        $this->reset('title', 'body', 'type');

        Flux::toast(variant: 'success', text: __('Notification sent.'));
    }

    public function markRead(int $id): void
    {
        Auth::user()->notificationsQuery()->findOrFail($id)->markAsRead();

        Flux::toast(variant: 'success', text: __('Marked as read.'));
    }

    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Notification>
     */
    #[Computed]
    public function notifications(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Auth::user()
            ->notificationsQuery()
            ->latest()
            ->paginate(10);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Developer - Notification') }}</flux:heading>

    <x-pages::developer.layout :heading="__('Notification')" :subheading="__('Send a test notification to verify real-time delivery')">
        <div class="grid gap-10 lg:grid-cols-2">
            <form wire:submit="send" class="space-y-6">
                <flux:input wire:model="title" :label="__('Title')" type="text" required placeholder="e.g. New order received" data-test="dev-notification-title" />

                <flux:textarea wire:model="body" :label="__('Body')" rows="4" placeholder="Optional notification body" data-test="dev-notification-body" />

                <flux:input wire:model="type" :label="__('Type')" type="text" placeholder="Optional, e.g. order.placed" data-test="dev-notification-type" />

                <flux:button variant="primary" type="submit" icon="paper-airplane" data-test="dev-notification-send">
                    {{ __('Send notification') }}
                </flux:button>
            </form>

            <div>
                <flux:heading size="lg">{{ __('Recent notifications') }}</flux:heading>

                <flux:separator class="mt-3" />

                <div class="mt-3">
                    @forelse ($this->notifications as $notification)
                        <div class="flex items-start justify-between gap-4 py-3" wire:key="notification-{{ $notification->id }}">
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $notification->isUnread() ? 'bg-red-500' : 'bg-transparent' }}"></span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ $notification->title }}</span>
                                        @if ($notification->isUnread())
                                            <flux:badge color="blue" inset="top bottom">{{ __('Unread') }}</flux:badge>
                                        @endif
                                    </div>
                                    @if ($notification->body)
                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $notification->body }}</flux:text>
                                    @endif
                                    <flux:text variant="subtle" class="text-xs">{{ $notification->created_at->diffForHumans() }}</flux:text>
                                </div>
                            </div>

                            @if ($notification->isUnread())
                                <flux:button variant="ghost" size="sm" wire:click="markRead({{ $notification->id }})">
                                    {{ __('Mark as read') }}
                                </flux:button>
                            @endif
                        </div>

                        @if (! $loop->last)
                            <flux:separator />
                        @endif
                    @empty
                        <flux:text class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No notifications yet.') }}
                        </flux:text>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $this->notifications->links() }}
                </div>
            </div>
        </div>
    </x-pages::developer.layout>
</section>
