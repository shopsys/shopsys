<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\Util\MenuManipulator;
use LogicException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\CrudRoleConstantProvider;
use Shopsys\AdministrationBundle\Component\Menu\CrudMenuSubscriber;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Shopsys\FrameworkBundle\Model\AdminNavigation\MenuItemPositioner;
use stdClass;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class CrudMenuSubscriberTest extends TestCase
{
    use SetTranslatorTrait;

    #[Override]
    protected function setUp(): void
    {
        $this->setTranslator();
    }

    public function testControllerWithMissingMenuSectionDoesNotHideTheFollowingControllers(): void
    {
        $rootMenu = $this->createRootMenu();

        $this->configureMenu($rootMenu, [MissingSectionCrudController::class, ProductsCrudController::class]);

        $this->assertNotNull($rootMenu->getChild('products')->getChild('admin_crud_products_list'));
        $this->assertNull($rootMenu->getChild('admin_crud_missing_section_list'));
    }

    public function testControllerWithMissingSubmenuSectionThrowsUnderstandableException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('CRUD controller "' . MissingSubmenuCrudController::class . '" is configured to be displayed in submenu section "nonexistent" of menu section "products"');

        $this->configureMenu($this->createRootMenu(), [MissingSubmenuCrudController::class]);
    }

    private function createRootMenu(): ItemInterface
    {
        $rootMenu = new MenuFactory()->createItem('root');
        $rootMenu->addChild('products');

        return $rootMenu;
    }

    /**
     * @param list<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>> $controllerClasses in the order the registry returns them
     */
    private function configureMenu(ItemInterface $rootMenu, array $controllerClasses): void
    {
        $controllers = [];
        $crudControllers = [];

        foreach ($controllerClasses as $controllerClass) {
            $controllers[$controllerClass] = static fn (): AdminBaseController => new $controllerClass();
            $crudControllers[] = ['class' => $controllerClass, 'entityClass' => stdClass::class];
        }

        $crudControllerRegistry = new CrudControllerRegistry(
            new EntityNameResolver([]),
            new CrudRoleConstantProvider(),
            new ServiceLocator($controllers),
            new ServiceLocator([]),
            $crudControllers,
        );
        $subscriber = new CrudMenuSubscriber(
            $crudControllerRegistry,
            new CrudRouteProvider($crudControllerRegistry, new InMemoryCache()),
            new MenuItemPositioner(new MenuManipulator()),
        );

        $subscriber->onConfigureMenu(new ConfigureMenuEvent(new MenuFactory(), $rootMenu));
    }
}

final class MissingSectionCrudController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config->setMenuSection('nonexistent');
    }
}

final class ProductsCrudController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config->setMenuSection('products');
    }
}

final class MissingSubmenuCrudController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config->setMenuSection('products', 'nonexistent');
    }
}
