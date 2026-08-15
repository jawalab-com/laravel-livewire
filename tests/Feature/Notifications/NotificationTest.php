<?php

use App\Enums\TeamRole;
use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->team = Team::where('slug', 'public')->firstOrCreate(
        ['slug' => 'public'],
        ['name' => 'Public', 'is_personal' => false],
    );

    $this->team->memberships()->updateOrCreate(
        ['user_id' => $this->user->id],
        ['role' => TeamRole::Member->value],
    );

    $this->user->switchTeam($this->team);
});

test('notifications index page can be rendered', function () {
    Notification::factory()->count(3)->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Notifications');
});

test('guests cannot view notifications', function () {
    $this->get(route('notifications.index'))
        ->assertRedirect(route('login'));
});

test('users cannot view other users notifications', function () {
    $other = User::factory()->create();
    $notification = Notification::factory()->create(['user_id' => $other->id]);

    $this->actingAs($this->user)
        ->get(route('notifications.show', $notification))
        ->assertForbidden();
});

test('viewing a notification marks it as read by default', function () {
    $notification = Notification::factory()->create(['user_id' => $this->user->id]);

    expect($notification->isUnread())->toBeTrue();

    $this->actingAs($this->user)
        ->get(route('notifications.show', $notification))
        ->assertOk();

    expect($notification->fresh()->isRead())->toBeTrue();
});

test('notifications can be marked as read explicitly', function () {
    $notification = Notification::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->post(route('notifications.read', $notification))
        ->assertRedirect();

    expect($notification->fresh()->isRead())->toBeTrue();
});

test('notifications can be marked as unread', function () {
    $notification = Notification::factory()->read()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->post(route('notifications.unread', $notification))
        ->assertRedirect();

    expect($notification->fresh()->isUnread())->toBeTrue();
});

test('users cannot mark other users notifications as read or unread', function () {
    $other = User::factory()->create();
    $unread = Notification::factory()->create(['user_id' => $other->id]);
    $read = Notification::factory()->read()->create(['user_id' => $other->id]);

    $this->actingAs($this->user)
        ->post(route('notifications.read', $unread))
        ->assertForbidden();

    $this->actingAs($this->user)
        ->post(route('notifications.unread', $read))
        ->assertForbidden();

    expect($unread->fresh()->isUnread())->toBeTrue()
        ->and($read->fresh()->isRead())->toBeTrue();
});

test('creating a notification broadcasts it on the user private channel', function () {
    Event::fake([NotificationCreated::class]);

    $notification = Notification::factory()->create(['user_id' => $this->user->id]);

    Event::assertDispatched(NotificationCreated::class, fn (NotificationCreated $event) => $event->notification->is($notification));

    $event = new NotificationCreated($notification);

    expect($event->broadcastOn())->toEqual([new PrivateChannel('App.Models.User.'.$this->user->id)])
        ->and($event->broadcastAs())->toBe('notification.created');
});
