<?php

use App\Enums\Permission as AppPermission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('unauthorized user cannot view roles page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertForbidden();
});

test('super admin can view roles page', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk();
});

test('user with roles.view permission can view roles page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(AppPermission::ViewRoles->value);

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk();
});

test('create role page can be rendered', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        AppPermission::ViewRoles->value,
        AppPermission::CreateRoles->value,
    ]);

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertOk();
});

test('user with roles.create permission can create a role with permissions via dedicated page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        AppPermission::ViewRoles->value,
        AppPermission::CreateRoles->value,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::roles.create')
        ->set('name', 'Editor')
        ->set('permissions', [
            AppPermission::ViewRoles->value,
            AppPermission::EditRoles->value,
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('roles.index'));

    $role = Role::where('name', 'Editor')->first();
    expect($role)->not->toBeNull();
    expect($role->hasPermissionTo(AppPermission::ViewRoles->value))->toBeTrue();
    expect($role->hasPermissionTo(AppPermission::EditRoles->value))->toBeTrue();
    expect($role->hasPermissionTo(AppPermission::DeleteRoles->value))->toBeFalse();
});

test('role name is required and unique when creating a role', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $this->actingAs($user);

    Livewire::test('pages::roles.create')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);

    Livewire::test('pages::roles.create')
        ->set('name', 'Super Admin')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('edit role page can be rendered', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $role = Role::create(['name' => 'Custom Role', 'guard_name' => 'web']);

    $this->actingAs($user)
        ->get(route('roles.edit', $role))
        ->assertOk();
});

test('user with roles.edit permission can update a role and its permissions via dedicated page', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $role = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
    $role->syncPermissions([AppPermission::ViewRoles->value]);

    $this->actingAs($user);

    Livewire::test('pages::roles.edit', ['role' => $role])
        ->assertSet('name', 'Manager')
        ->set('name', 'Lead Manager')
        ->set('permissions', [
            AppPermission::ViewRoles->value,
            AppPermission::CreateRoles->value,
            AppPermission::EditRoles->value,
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('roles.index'));

    $role->refresh();
    expect($role->name)->toEqual('Lead Manager');
    expect($role->hasPermissionTo(AppPermission::CreateRoles->value))->toBeTrue();
});

test('user with roles.delete permission can delete a custom role', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $role = Role::create(['name' => 'Temporary Role', 'guard_name' => 'web']);

    $this->actingAs($user);

    Livewire::test('pages::roles.index')
        ->call('confirmDelete', $role->id)
        ->assertSet('deletingRoleId', $role->id)
        ->call('deleteRole')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', name: 'delete-role-modal');

    expect(Role::where('name', 'Temporary Role')->exists())->toBeFalse();
});

test('super admin role cannot be deleted', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $superAdminRole = Role::where('name', 'Super Admin')->first();

    $this->actingAs($user);

    Livewire::test('pages::roles.index')
        ->call('confirmDelete', $superAdminRole->id)
        ->assertForbidden();

    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();
});
