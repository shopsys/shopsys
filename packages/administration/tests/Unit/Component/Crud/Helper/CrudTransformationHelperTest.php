<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;

class CrudTransformationHelperTest extends TestCase
{
    #[DataProvider('transformToRouteNameDataProvider')]
    public function testTransformToRouteName(string $controllerName, string $expectedRouteName): void
    {
        $result = CrudTransformationHelper::transformToRouteName($controllerName);

        $this->assertSame($expectedRouteName, $result);
    }

    /**
     * @return array<string, array{controllerName: string, expectedRouteName: string}>
     */
    public static function transformToRouteNameDataProvider(): array
    {
        return [
            'PriceListController to snake_case' => [
                'controllerName' => 'PriceListController',
                'expectedRouteName' => 'price_list',
            ],
            'OrdersController to snake_case' => [
                'controllerName' => 'OrdersController',
                'expectedRouteName' => 'orders',
            ],
            'UserRoleCrudController to snake_case' => [
                'controllerName' => 'UserRoleCrudController',
                'expectedRouteName' => 'user_role',
            ],
            'ProductCrudController to snake_case' => [
                'controllerName' => 'ProductCrudController',
                'expectedRouteName' => 'product',
            ],
            'SimpleController to snake_case' => [
                'controllerName' => 'SimpleController',
                'expectedRouteName' => 'simple',
            ],
            'MultiWordNameController to snake_case' => [
                'controllerName' => 'MultiWordNameController',
                'expectedRouteName' => 'multi_word_name',
            ],
            'ABCController with consecutive capitals' => [
                'controllerName' => 'ABCController',
                'expectedRouteName' => 'abc',
            ],
            'class name without suffix' => [
                'controllerName' => 'PriceList',
                'expectedRouteName' => 'price_list',
            ],
        ];
    }

    #[DataProvider('generateControllerDataProvider')]
    public function testGenerateController(
        string $controllerClass,
        ActionType $actionType,
        string $expectedController,
    ): void {
        $result = CrudTransformationHelper::generateController($controllerClass, $actionType);

        $this->assertSame($expectedController, $result);
    }

    /**
     * @return array<string, array{controllerClass: string, actionType: \Shopsys\AdministrationBundle\Component\Config\ActionType, expectedController: string}>
     */
    public static function generateControllerDataProvider(): array
    {
        return [
            'list action' => [
                'controllerClass' => 'App\\Controller\\ProductController',
                'actionType' => ActionType::LIST,
                'expectedController' => 'App\\Controller\\ProductController::listAction',
            ],
            'detail action' => [
                'controllerClass' => 'App\\Controller\\OrderController',
                'actionType' => ActionType::DETAIL,
                'expectedController' => 'App\\Controller\\OrderController::detailAction',
            ],
            'create action' => [
                'controllerClass' => 'App\\Controller\\UserController',
                'actionType' => ActionType::CREATE,
                'expectedController' => 'App\\Controller\\UserController::createAction',
            ],
            'edit action' => [
                'controllerClass' => 'App\\Controller\\CategoryController',
                'actionType' => ActionType::EDIT,
                'expectedController' => 'App\\Controller\\CategoryController::editAction',
            ],
            'delete action' => [
                'controllerClass' => 'App\\Controller\\PriceListController',
                'actionType' => ActionType::DELETE,
                'expectedController' => 'App\\Controller\\PriceListController::deleteAction',
            ],
        ];
    }

    #[DataProvider('transformToRouteUrlDataProvider')]
    public function testTransformToRouteUrl(string $controllerName, string $expectedRouteUrl): void
    {
        $result = CrudTransformationHelper::transformToRouteUrl($controllerName);

        $this->assertSame($expectedRouteUrl, $result);
    }

    /**
     * @return array<string, array{controllerName: string, expectedRouteUrl: string}>
     */
    public static function transformToRouteUrlDataProvider(): array
    {
        return [
            'PriceListController to kebab-case' => [
                'controllerName' => 'PriceListController',
                'expectedRouteUrl' => 'price-list',
            ],
            'OrdersController to kebab-case' => [
                'controllerName' => 'OrdersController',
                'expectedRouteUrl' => 'orders',
            ],
            'UserRoleCrudController to kebab-case' => [
                'controllerName' => 'UserRoleCrudController',
                'expectedRouteUrl' => 'user-role',
            ],
            'ProductCrudController to kebab-case' => [
                'controllerName' => 'ProductCrudController',
                'expectedRouteUrl' => 'product',
            ],
            'SimpleController to kebab-case' => [
                'controllerName' => 'SimpleController',
                'expectedRouteUrl' => 'simple',
            ],
            'MultiWordNameController to kebab-case' => [
                'controllerName' => 'MultiWordNameController',
                'expectedRouteUrl' => 'multi-word-name',
            ],
            'ABCController with consecutive capitals' => [
                'controllerName' => 'ABCController',
                'expectedRouteUrl' => 'abc',
            ],
            'class name without suffix' => [
                'controllerName' => 'PriceList',
                'expectedRouteUrl' => 'price-list',
            ],
        ];
    }

    #[DataProvider('toSingularEntityNameDataProvider')]
    public function testToSingularEntityName(string $entityName, string $expectedSingularName): void
    {
        $result = CrudTransformationHelper::toSingularEntityName($entityName);

        $this->assertSame($expectedSingularName, $result);
    }

    /**
     * @return array<string, array{entityName: string, expectedSingularName: string}>
     */
    public static function toSingularEntityNameDataProvider(): array
    {
        return [
            'single word' => [
                'entityName' => 'Category',
                'expectedSingularName' => 'Category',
            ],
            'multi word' => [
                'entityName' => 'OrderItem',
                'expectedSingularName' => 'Order item',
            ],
            'word with false inflector singular candidate' => [
                'entityName' => 'AdditionalService',
                'expectedSingularName' => 'Additional service',
            ],
            'plural entity name' => [
                'entityName' => 'AdditionalServices',
                'expectedSingularName' => 'Additional service',
            ],
            'word with an explicit inflector rule' => [
                'entityName' => 'OrderStatus',
                'expectedSingularName' => 'Order status',
            ],
            'known limitation - candidate dropping a trailing "s" passes the round-trip check' => [
                'entityName' => 'DeliveryAddress',
                'expectedSingularName' => 'Delivery addres',
            ],
        ];
    }

    #[DataProvider('toPluralEntityNameDataProvider')]
    public function testToPluralEntityName(string $entityName, string $expectedPluralName): void
    {
        $result = CrudTransformationHelper::toPluralEntityName($entityName);

        $this->assertSame($expectedPluralName, $result);
    }

    /**
     * @return array<string, array{entityName: string, expectedPluralName: string}>
     */
    public static function toPluralEntityNameDataProvider(): array
    {
        return [
            'single word' => [
                'entityName' => 'Category',
                'expectedPluralName' => 'Categories',
            ],
            'multi word' => [
                'entityName' => 'OrderItem',
                'expectedPluralName' => 'Order items',
            ],
            'word ending with ice' => [
                'entityName' => 'AdditionalService',
                'expectedPluralName' => 'Additional services',
            ],
        ];
    }

    #[DataProvider('generateRoleConstantDataProvider')]
    public function testGenerateRoleConstant(
        string $controllerName,
        ?string $customRoleConstant,
        string $expectedRoleConstant,
    ): void {
        $result = CrudTransformationHelper::generateRoleConstant($controllerName, $customRoleConstant);

        $this->assertSame($expectedRoleConstant, $result);
    }

    /**
     * @return array<string, array{controllerName: string, customRoleConstant: string|null, expectedRoleConstant: string}>
     */
    public static function generateRoleConstantDataProvider(): array
    {
        return [
            'generates from controller name' => [
                'controllerName' => 'OrderController',
                'customRoleConstant' => null,
                'expectedRoleConstant' => 'ROLE_CRUD_ORDER',
            ],
            'generates from multi-word controller name' => [
                'controllerName' => 'PriceListController',
                'customRoleConstant' => null,
                'expectedRoleConstant' => 'ROLE_CRUD_PRICE_LIST',
            ],
            'generates from CrudController suffix' => [
                'controllerName' => 'UserRoleCrudController',
                'customRoleConstant' => null,
                'expectedRoleConstant' => 'ROLE_CRUD_USER_ROLE',
            ],
            'uses custom role constant when provided' => [
                'controllerName' => 'OrderController',
                'customRoleConstant' => 'ROLE_CUSTOM_ORDER',
                'expectedRoleConstant' => 'ROLE_CUSTOM_ORDER',
            ],
            'custom role constant takes priority' => [
                'controllerName' => 'PriceListController',
                'customRoleConstant' => 'ROLE_PRICING',
                'expectedRoleConstant' => 'ROLE_PRICING',
            ],
        ];
    }
}
