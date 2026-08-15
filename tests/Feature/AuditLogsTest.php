<?php

use App\Enums\Permission as AppPermission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('unauthorized user cannot view audit logs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('audit-logs.index'))
        ->assertForbidden();
});

test('super admin can view audit logs page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin)
        ->get(route('audit-logs.index'))
        ->assertOk();
});

test('user with audit_logs.view permission can view audit logs page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(AppPermission::ViewAuditLogs->value);

    $this->actingAs($user)
        ->get(route('audit-logs.index'))
        ->assertOk();
});

test('updating a user model automatically creates an activity log with diffs', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin);

    $user = User::factory()->create(['name' => 'Original Name']);
    $user->update(['name' => 'Updated Name']);

    $activity = Activity::where('subject_type', User::class)
        ->where('subject_id', $user->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->attribute_changes['old']['name'])->toEqual('Original Name');
    expect($activity->attribute_changes['attributes']['name'])->toEqual('Updated Name');
});

test('audit logs page can be searched and inspected', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    activity()
        ->causedBy($admin)
        ->event('custom')
        ->withProperties(['key' => 'custom_val'])
        ->log('Specific custom activity test');

    $this->actingAs($admin);

    $activity = Activity::where('description', 'Specific custom activity test')->first();

    Livewire::test('pages::audit-logs.index')
        ->set('search', 'Specific custom')
        ->assertSee('Specific custom activity test')
        ->call('inspectActivity', $activity->id)
        ->assertSet('selectedActivityId', $activity->id)
        ->assertSet('showDetailsModal', true);
});

test('audit logs can be filtered by event type', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    activity()->event('created')->log('Entity created');
    activity()->event('deleted')->log('Entity deleted');

    $this->actingAs($admin);

    Livewire::test('pages::audit-logs.index')
        ->set('eventFilter', 'deleted')
        ->assertSee('Entity deleted')
        ->assertDontSee('Entity created');
});
