<?php

namespace App\Enums;

enum Permission: string
{
    // Roles
    case ViewRoles = 'roles.view';
    case CreateRoles = 'roles.create';
    case EditRoles = 'roles.edit';
    case DeleteRoles = 'roles.delete';

    // Users
    case ViewUsers = 'users.view';
    case CreateUsers = 'users.create';
    case EditUsers = 'users.edit';
    case DeleteUsers = 'users.delete';

    // Audit Logs
    case ViewAuditLogs = 'audit_logs.view';

    /**
     * Get the display label for the permission.
     */
    public function label(): string
    {
        return match ($this) {
            self::ViewRoles => 'View Roles',
            self::CreateRoles => 'Create Roles',
            self::EditRoles => 'Edit Roles',
            self::DeleteRoles => 'Delete Roles',
            self::ViewUsers => 'View Users',
            self::CreateUsers => 'Create Users',
            self::EditUsers => 'Edit Users',
            self::DeleteUsers => 'Delete Users',
            self::ViewAuditLogs => 'View Audit Logs',
        };
    }

    /**
     * Get the explanation/description of what this permission allows.
     */
    public function description(): string
    {
        return match ($this) {
            self::ViewRoles => 'Ability to browse and view roles and their assigned permissions.',
            self::CreateRoles => 'Ability to define and create new roles.',
            self::EditRoles => 'Ability to update existing roles and configure their permissions.',
            self::DeleteRoles => 'Ability to permanently delete roles.',
            self::ViewUsers => 'Ability to browse and view users in the system.',
            self::CreateUsers => 'Ability to create and invite new user accounts.',
            self::EditUsers => 'Ability to update user accounts and modify their assigned roles.',
            self::DeleteUsers => 'Ability to delete user accounts from the system.',
            self::ViewAuditLogs => 'Ability to inspect application activity, events, and audit logs.',
        };
    }

    /**
     * Group permissions logically for UI display.
     */
    public function group(): string
    {
        return match ($this) {
            self::ViewRoles,
            self::CreateRoles,
            self::EditRoles,
            self::DeleteRoles => 'Role Management',

            self::ViewUsers,
            self::CreateUsers,
            self::EditUsers,
            self::DeleteUsers => 'User Management',

            self::ViewAuditLogs => 'System Auditing',
        };
    }

    /**
     * Get all permission values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
