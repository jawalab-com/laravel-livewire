<?php

use App\Enums\Permission as AppPermission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('unauthorized user cannot view users page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('super admin can view users page', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk();
});

test('user with users.view permission can view users page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(AppPermission::ViewUsers->value);

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk();
});

test('create user page can be rendered', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        AppPermission::ViewUsers->value,
        AppPermission::CreateUsers->value,
    ]);

    $this->actingAs($user)
        ->get(route('users.create'))
        ->assertOk();
});

test('user with users.create permission can create a user and assign roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $this->actingAs($admin);

    Livewire::test('pages::users.create')
        ->set('name', 'John Doe')
        ->set('email', 'johndoe@example.com')
        ->set('password', 'password123456')
        ->set('selectedRoles', ['Editor'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    $newUser = User::where('email', 'johndoe@example.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->name)->toEqual('John Doe');
    expect($newUser->hasRole('Editor'))->toBeTrue();
});

test('create user requires valid name, email, and password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin);

    Livewire::test('pages::users.create')
        ->set('name', '')
        ->set('email', 'invalid-email')
        ->set('password', '')
        ->call('save')
        ->assertHasErrors(['name', 'email', 'password']);
});

test('edit user page can be rendered', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $user = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('users.edit', $user))
        ->assertOk();
});

test('user with users.edit permission can update a user and sync roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $role = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    $this->actingAs($admin);

    Livewire::test('pages::users.edit', ['user' => $user])
        ->assertSet('name', 'Old Name')
        ->assertSet('email', 'old@example.com')
        ->set('name', 'New Name')
        ->set('email', 'new@example.com')
        ->set('selectedRoles', ['Manager'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    $user->refresh();
    expect($user->name)->toEqual('New Name');
    expect($user->email)->toEqual('new@example.com');
    expect($user->hasRole('Manager'))->toBeTrue();
});

test('user with users.delete permission can delete a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $userToDelete = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test('pages::users.index')
        ->call('confirmDelete', $userToDelete->id)
        ->assertSet('deletingUserId', $userToDelete->id)
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', name: 'delete-user-modal');

    expect(User::where('id', $userToDelete->id)->exists())->toBeFalse();
});

test('user cannot delete themselves via user management', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin);

    Livewire::test('pages::users.index')
        ->call('confirmDelete', $admin->id)
        ->assertForbidden();

    expect(User::where('id', $admin->id)->exists())->toBeTrue();
});

test('last super admin cannot be deleted', function () {
    $admin1 = User::factory()->create();
    $admin1->assignRole('Super Admin');

    $admin2 = User::factory()->create();
    $admin2->givePermissionTo([
        AppPermission::ViewUsers->value,
        AppPermission::DeleteUsers->value,
    ]);

    $this->actingAs($admin2);

    Livewire::test('pages::users.index')
        ->call('confirmDelete', $admin1->id)
        ->assertForbidden();

    expect(User::where('id', $admin1->id)->exists())->toBeTrue();
});
