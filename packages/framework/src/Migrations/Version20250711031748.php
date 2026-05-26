<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250711031748 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE administrator_role_groups ADD system_managed BOOLEAN DEFAULT FALSE NOT NULL');
        $this->sql('ALTER TABLE administrator_role_groups ALTER system_managed DROP DEFAULT');

        $this->sql('INSERT INTO administrator_role_groups (name, roles, system_managed) VALUES (:name, :roles, :system_managed)', [
            'name' => SystemRole::ALL,
            'roles' => '["' . SystemRole::ALL . '"]',
            'system_managed' => true,
        ]);

        $this->sql('INSERT INTO administrator_role_groups (name, roles, system_managed) VALUES (:name, :roles, :system_managed)', [
            'name' => SystemRole::ALL_VIEW,
            'roles' => '["' . SystemRole::ALL_VIEW . '"]',
            'system_managed' => true,
        ]);

        $this->migrateAdministratorSystemRolesToRoleGroups();
    }

    /**
     * Migrates administrators from having ROLE_ALL or ROLE_ALL_VIEW directly
     * to using the corresponding system-managed role groups
     */
    private function migrateAdministratorSystemRolesToRoleGroups(): void
    {
        $roleAllGroupId = $this->connection->fetchOne(
            'SELECT id FROM administrator_role_groups WHERE name = :name',
            ['name' => SystemRole::ALL],
        );

        $roleAllViewGroupId = $this->connection->fetchOne(
            'SELECT id FROM administrator_role_groups WHERE name = :name',
            ['name' => SystemRole::ALL_VIEW],
        );

        $this->migrateSuperAdministrators();

        $administratorsWithSystemRoles = $this->connection->fetchAllAssociative(
            'SELECT DISTINCT administrator_id, 
                    MAX(CASE WHEN role = :role_all THEN 1 ELSE 0 END) as has_role_all
             FROM administrator_roles 
             WHERE role IN (:role_all, :role_all_view)
             GROUP BY administrator_id',
            [
                'role_all' => SystemRole::ALL,
                'role_all_view' => SystemRole::ALL_VIEW,
            ],
        );

        foreach ($administratorsWithSystemRoles as $admin) {
            $administratorId = $admin['administrator_id'];

            // Determine which role group to assign
            $roleGroupId = $admin['has_role_all'] ? $roleAllGroupId : $roleAllViewGroupId;

            $this->sql(
                'UPDATE administrators SET role_group_id = :role_group_id WHERE id = :administrator_id',
                [
                    'role_group_id' => $roleGroupId,
                    'administrator_id' => $administratorId,
                ],
            );

            // Remove all individual roles for this administrator
            $this->sql(
                'DELETE FROM administrator_roles WHERE administrator_id = :administrator_id',
                ['administrator_id' => $administratorId],
            );
        }
    }

    public function migrateSuperAdministrators(): void
    {
        $superAdministratorIds = $this->connection->fetchAllAssociative(
            'SELECT administrator_id FROM administrator_roles WHERE role = :superadmin_role',
            ['superadmin_role' => SystemRole::SUPER_ADMIN],
        );

        foreach ($superAdministratorIds as $superAdministratorId) {
            $this->sql(
                'UPDATE administrators SET role_group_id = NULL WHERE id = :administrator_id AND role_group_id IS NOT NULL',
                ['administrator_id' => $superAdministratorId['administrator_id']],
            );

            $this->sql(
                'DELETE FROM administrator_roles WHERE administrator_id = :administrator_id AND role != :superadmin_role',
                [
                    'administrator_id' => $superAdministratorId['administrator_id'],
                    'superadmin_role' => SystemRole::SUPER_ADMIN,
                ],
            );
        }
    }
}
