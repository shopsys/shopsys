<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\MandatoryAdministratorRoleIsMissingException;
use Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole;
use Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleData;
use Tests\FrameworkBundle\Unit\TestCase;

class AdministratorTest extends TestCase
{
    /**
     * @return array<'expectedRole'|'roles', 'ROLE_ADMIN'[]|'ROLE_PRODUCTS'[]|'ROLE_CATEGORIES'[]|'ROLE_ADMIN'>[]|array<'expectedRole'|'roles', 'ROLE_SUPER_ADMIN'[]|'ROLE_PRODUCTS'[]|'ROLE_CATEGORIES'[]|'ROLE_SUPER_ADMIN'>[]
     */
    public function administratorRolesDataProvider(): array
    {
        return [
            [
                'roles' => ['ROLE_ADMIN', 'ROLE_PRODUCTS', 'ROLE_CATEGORIES'],
                'expectedRole' => 'ROLE_ADMIN',
            ],
            [
                'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_PRODUCTS', 'ROLE_CATEGORIES'],
                'expectedRole' => 'ROLE_SUPER_ADMIN',
            ],
        ];
    }

    /**
     * @dataProvider administratorRolesDataProvider
     * @param mixed[] $roles
     * @param string $expectedRole
     */
    public function testSetAdministratorRolesWithMandatoryRole(array $roles, string $expectedRole): void
    {
        $administratorData = new AdministratorData();
        $administratorData->realName = 'Administrator';
        $administratorData->username = 'admin';
        $administratorData->email = 'no-reply@shopsys.com';
        $administratorData->password = 'pa55w0rd';

        $administrator = new Administrator($administratorData);

        $administratorRoles = [];

        foreach ($roles as $role) {
            $administratorRoleData = new AdministratorRoleData();
            $administratorRoleData->administrator = $administrator;
            $administratorRoleData->role = $role;
            $administratorRoles[] = new AdministratorRole($administratorRoleData);
        }
        $administrator->addRoles($administratorRoles);

        $this->assertContains($expectedRole, $administrator->getRoles());
    }

    public function testSetAdministratorRolesWithoutMandatoryRole(): void
    {
        $administratorData = new AdministratorData();
        $administratorData->realName = 'Administrator';
        $administratorData->username = 'admin';
        $administratorData->email = 'no-reply@shopsys.com';
        $administratorData->password = 'pa55w0rd';

        $administrator = new Administrator($administratorData);

        $this->setValueOfProtectedProperty($administrator, 'id', 1);

        $administratorRoleData = new AdministratorRoleData();
        $administratorRoleData->administrator = $administrator;
        $administratorRoleData->role = 'ROLE_PRODUCTS';
        $administratorRoles[] = new AdministratorRole($administratorRoleData);

        $this->expectException(MandatoryAdministratorRoleIsMissingException::class);

        $administrator->addRoles($administratorRoles);
    }
}
