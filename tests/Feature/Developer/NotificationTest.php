<?php

use App\Enums\TeamRole;
use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

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

test('developer notification page can be rendered', function () {
    $this->actingAs($this->user)
        ->get(route('developer.notification'))
        ->assertOk()
        ->assertSee('Notification');
});

test('guests cannot access developer page', function () {
    $this->get(route('developer.notification'))
        ->assertRedirect(route('login'));
});

test('developer page can send a notification', function () {
    Event::fake([NotificationCreated::class]);
    $this->actingAs($this->user);

    Livewire::test('pages::developer.notification')
        ->set('title', 'Test notification')
        ->set('body', 'Hello from the developer page')
        ->set('type', 'test')
        ->call('send')
        ->assertHasNoErrors();

    $notification = Notification::query()->where('user_id', $this->user->id)->first();

    expect($notification)->not->toBeNull()
        ->and($notification->title)->toBe('Test notification')
        ->and($notification->body)->toBe('Hello from the developer page')
        ->and($notification->type)->toBe('test');

    Event::assertDispatched(NotificationCreated::class, fn (NotificationCreated $event) => $event->notification->is($notification));
});

test('developer page requires a title to send a notification', function () {
    $this->actingAs($this->user);

    Livewire::test('pages::developer.notification')
        ->set('title', '')
        ->call('send')
        ->assertHasErrors(['title' => 'required']);

    expect(Notification::query()->where('user_id', $this->user->id)->exists())->toBeFalse();
});

test('developer page can mark a notification as read', function () {
    $notification = Notification::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);

    Livewire::test('pages::developer.notification')
        ->call('markRead', $notification->id)
        ->assertHasNoErrors();

    expect($notification->fresh()->isRead())->toBeTrue();
});

test('developer page is not available outside local environments', function () {
    $this->app->detectEnvironment(fn () => 'production');

    $this->actingAs($this->user)
        ->get(route('developer.notification'))
        ->assertNotFound();
});
