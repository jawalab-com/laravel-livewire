<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public function unreadCount(): int
    {
        return Auth::user()->unreadNotificationsCount();
    }

    public function notifications(): Collection
    {
        return Auth::user()
            ->notificationsQuery()
            ->latest()
            ->limit(5)
            ->get();
    }

    public function getListeners(): array
    {
        return [
            'echo-private:App.Models.User.'.Auth::id().',notification.created' => 'refresh',
        ];
    }

    public function refresh(): void
    {
        //
    }

    public function render(): View
    {
        return view('livewire.notification-dropdown');
    }
}
