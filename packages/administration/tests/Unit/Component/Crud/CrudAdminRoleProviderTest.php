<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\CrudAdminRoleProvider;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;

class CrudAdminRoleProviderTest extends TestCase
{
    private const string ROLE_CONSTANT = 'ROLE_CRUD_REVIEW';

    public function testCustomActionGuardedByCanAttributeRequiresPermissionOnTheControllerRole(): void
    {
        $roleCollection = $this->configureRoles([]);

        // the controller has only the LIST action (VIEW) enabled among the built-in ones, EDIT comes from the approve custom action
        $this->assertEqualsCanonicalizing([Permission::EDIT, Permission::VIEW], $roleCollection->get(self::ROLE_CONSTANT)->getAvailablePermissions());
    }

    public function testCustomActionGuardedByAnotherRoleDoesNotInfluenceTheControllerRole(): void
    {
        $roleCollection = $this->configureRoles(
            [ReviewCrudControllerRegistryFactory::createActionData('reject', [
                'accessControlRules' => [['roleIdentifier' => 'ROLE_MODERATOR_DELETE', 'httpMethods' => []]],
            ])],
            static function (CrudConfig $config): void {
                $config->disableAction(['approve', 'move']);
            },
        );

        $this->assertSame([Permission::VIEW], $roleCollection->get(self::ROLE_CONSTANT)->getAvailablePermissions());
    }

    public function testDisabledCustomActionDoesNotRequireItsPermission(): void
    {
        $roleCollection = $this->configureRoles(
            [],
            static function (CrudConfig $config): void {
                $config->disableAction(['approve', 'move']);
            },
        );

        $this->assertSame([Permission::VIEW], $roleCollection->get(self::ROLE_CONSTANT)->getAvailablePermissions());
    }

    /**
     * @param array<int, array<string, mixed>> $crudActions
     * @param callable(\Shopsys\AdministrationBundle\Component\Config\CrudConfig): void|null $configure
     */
    private function configureRoles(array $crudActions, ?callable $configure = null): RoleCollection
    {
        $controller = $this->createController($configure);
        $roleProvider = new CrudAdminRoleProvider(ReviewCrudControllerRegistryFactory::create($crudActions, $controller));
        $roleCollection = new RoleCollection();

        $roleProvider->configureRoles($roleCollection);

        return $roleCollection;
    }

    /**
     * The menu title is set explicitly because the derived one would need the static translator
     *
     * @param callable(\Shopsys\AdministrationBundle\Component\Config\CrudConfig): void|null $configure
     */
    private function createController(?callable $configure): AbstractCrudController
    {
        return new class($configure) extends ReviewCrudController {
            /**
             * @param callable(\Shopsys\AdministrationBundle\Component\Config\CrudConfig): void|null $configureCallback
             */
            public function __construct(private $configureCallback)
            {
            }

            #[Override]
            public function configure(CrudConfig $config): void
            {
                $config->setMenuTitle('Reviews');

                if ($this->configureCallback !== null) {
                    ($this->configureCallback)($config);
                }
            }
        };
    }
}
