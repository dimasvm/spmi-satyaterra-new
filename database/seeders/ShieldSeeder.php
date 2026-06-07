<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["dashboard.view","users.view","users.create","users.update","users.delete","users.impersonate","roles.view","roles.create","roles.update","roles.delete","units.view","units.create","units.update","units.delete","spmi-periods.view","spmi-periods.create","spmi-periods.update","spmi-periods.delete","standard-categories.view","standard-categories.create","standard-categories.update","standard-categories.delete","quality-standards.view","quality-standards.create","quality-standards.update","quality-standards.submit","quality-standards.approve","quality-standards.delete","standard-indicators.view","standard-indicators.create","standard-indicators.update","standard-indicators.delete","indicator-assignments.view","indicator-assignments.create","indicator-assignments.update","indicator-assignments.delete","indicator-achievements.view","indicator-achievements.create","indicator-achievements.update","indicator-achievements.submit","indicator-achievements.review","achievement-evidences.view","achievement-evidences.create","achievement-evidences.delete","ami-periods.view","ami-periods.create","ami-periods.update","ami-periods.delete","ami-audits.view","ami-audits.create","ami-audits.update","ami-audits.finalize","ami-checklists.view","ami-checklists.create","ami-checklists.update","ami-findings.view","ami-findings.create","ami-findings.update","corrective-actions.view","corrective-actions.create","corrective-actions.update","corrective-actions.submit","corrective-actions.review","corrective-action-evidences.view","corrective-action-evidences.create","corrective-action-evidences.delete","quality-documents.view","quality-documents.create","quality-documents.update","quality-documents.approve","quality-documents.delete","activity-logs.view","reports.view","reports.export","notifications.view","notifications.update","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","DeleteAny:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:IndicatorUnitAssignment","View:IndicatorUnitAssignment","Create:IndicatorUnitAssignment","Update:IndicatorUnitAssignment","Delete:IndicatorUnitAssignment","DeleteAny:IndicatorUnitAssignment","Restore:IndicatorUnitAssignment","ForceDelete:IndicatorUnitAssignment","ForceDeleteAny:IndicatorUnitAssignment","RestoreAny:IndicatorUnitAssignment","Replicate:IndicatorUnitAssignment","Reorder:IndicatorUnitAssignment","ViewAny:QualityStandard","View:QualityStandard","Create:QualityStandard","Update:QualityStandard","Delete:QualityStandard","DeleteAny:QualityStandard","Restore:QualityStandard","ForceDelete:QualityStandard","ForceDeleteAny:QualityStandard","RestoreAny:QualityStandard","Replicate:QualityStandard","Reorder:QualityStandard","ViewAny:SpmiPeriod","View:SpmiPeriod","Create:SpmiPeriod","Update:SpmiPeriod","Delete:SpmiPeriod","DeleteAny:SpmiPeriod","Restore:SpmiPeriod","ForceDelete:SpmiPeriod","ForceDeleteAny:SpmiPeriod","RestoreAny:SpmiPeriod","Replicate:SpmiPeriod","Reorder:SpmiPeriod","ViewAny:StandardCategory","View:StandardCategory","Create:StandardCategory","Update:StandardCategory","Delete:StandardCategory","DeleteAny:StandardCategory","Restore:StandardCategory","ForceDelete:StandardCategory","ForceDeleteAny:StandardCategory","RestoreAny:StandardCategory","Replicate:StandardCategory","Reorder:StandardCategory","ViewAny:StandardIndicator","View:StandardIndicator","Create:StandardIndicator","Update:StandardIndicator","Delete:StandardIndicator","DeleteAny:StandardIndicator","Restore:StandardIndicator","ForceDelete:StandardIndicator","ForceDeleteAny:StandardIndicator","RestoreAny:StandardIndicator","Replicate:StandardIndicator","Reorder:StandardIndicator","ViewAny:Unit","View:Unit","Create:Unit","Update:Unit","Delete:Unit","DeleteAny:Unit","Restore:Unit","ForceDelete:Unit","ForceDeleteAny:Unit","RestoreAny:Unit","Replicate:Unit","Reorder:Unit","ViewAny:User","View:User","Create:User","Update:User","Delete:User","DeleteAny:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","View:IndicatorUnitAssignmentTable"]},{"name":"admin_lpm","guard_name":"web","permissions":["dashboard.view","units.view","spmi-periods.view","standard-categories.view","quality-standards.view","standard-indicators.view","quality-documents.view","reports.view","units.create","units.update","spmi-periods.create","spmi-periods.update","standard-categories.create","standard-categories.update","quality-standards.create","quality-standards.update","quality-standards.submit","quality-standards.approve","standard-indicators.create","standard-indicators.update","indicator-assignments.view","indicator-assignments.create","indicator-assignments.update","indicator-achievements.view","indicator-achievements.review","achievement-evidences.view","quality-documents.create","quality-documents.update","quality-documents.approve","reports.export","notifications.view","notifications.update","ami-periods.view","ami-periods.create","ami-periods.update","ami-audits.view","ami-audits.create","ami-audits.update","ami-audits.finalize","ami-checklists.view","ami-checklists.create","ami-checklists.update","ami-findings.view","ami-findings.create","ami-findings.update","corrective-actions.view","corrective-actions.review","corrective-action-evidences.view","users.view","users.create","users.update","roles.view","activity-logs.view"]},{"name":"pimpinan","guard_name":"web","permissions":["dashboard.view","units.view","spmi-periods.view","standard-categories.view","quality-standards.view","quality-standards.approve","standard-indicators.view","indicator-achievements.view","achievement-evidences.view","ami-periods.view","ami-audits.view","ami-findings.view","corrective-actions.view","quality-documents.view","quality-documents.approve","reports.view","reports.export","notifications.view","notifications.update"]},{"name":"unit_pic","guard_name":"web","permissions":["dashboard.view","units.view","spmi-periods.view","standard-categories.view","quality-standards.view","standard-indicators.view","quality-documents.view","reports.view","indicator-achievements.view","indicator-achievements.create","indicator-achievements.update","indicator-achievements.submit","achievement-evidences.view","achievement-evidences.create","achievement-evidences.delete","corrective-actions.view","corrective-actions.create","corrective-actions.update","corrective-actions.submit","corrective-action-evidences.view","corrective-action-evidences.create","corrective-action-evidences.delete","notifications.view","notifications.update","indicator-assignments.view","ami-audits.view","ami-checklists.view","ami-findings.view"]},{"name":"auditor","guard_name":"web","permissions":["dashboard.view","units.view","quality-standards.view","standard-indicators.view","indicator-achievements.view","achievement-evidences.view","ami-periods.view","ami-audits.view","ami-checklists.view","ami-checklists.create","ami-checklists.update","ami-findings.view","ami-findings.create","ami-findings.update","corrective-actions.view","corrective-actions.review","corrective-action-evidences.view","quality-documents.view","reports.view","notifications.view","notifications.update"]},{"name":"viewer","guard_name":"web","permissions":["dashboard.view","units.view","spmi-periods.view","standard-categories.view","quality-standards.view","standard-indicators.view","indicator-achievements.view","achievement-evidences.view","ami-periods.view","ami-audits.view","ami-findings.view","corrective-actions.view","quality-documents.view","reports.view","notifications.view"]}]';
        $directPermissions = '[]';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
