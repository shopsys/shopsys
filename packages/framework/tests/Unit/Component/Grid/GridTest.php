<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Grid;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Twig\Environment;

class GridTest extends TestCase
{
    public function testGetParametersFromRequest()
    {
        $getParameters = [
            Grid::GET_PARAMETER => [
                'gridId' => [
                    'limit' => '100',
                    'page' => '3',
                    'order' => '-name',
                ],
            ],
        ];

        $request = new Request($getParameters);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );

        $this->assertSame('gridId', $grid->getId());
        $this->assertSame(100, $grid->getLimit());
        $this->assertSame(3, $grid->getPage());
        $this->assertSame('name', $grid->getOrderSourceColumnName());
        $this->assertSame('desc', $grid->getOrderDirection());
    }

    public function testAddColumn()
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );
        $grid->addColumn('columnId1', 'sourceColumnName1', 'title1', true)->setClassAttribute('classAttribute');
        $grid->addColumn('columnId2', 'sourceColumnName2', 'title2', false);
        $columns = $grid->getColumnsById();

        $this->assertCount(2, $columns);
        /** @var \Shopsys\FrameworkBundle\Component\Grid\Column $column2 */
        $column2 = array_pop($columns);
        /** @var \Shopsys\FrameworkBundle\Component\Grid\Column $column1 */
        $column1 = array_pop($columns);
        $this->assertSame('columnId1', $column1->getId());
        $this->assertSame('sourceColumnName1', $column1->getSourceColumnName());
        $this->assertSame('title1', $column1->getTitle());
        $this->assertSame(true, $column1->isSortable());
        $this->assertSame('classAttribute', $column1->getClassAttribute());

        $this->assertSame('columnId2', $column2->getId());
        $this->assertSame('sourceColumnName2', $column2->getSourceColumnName());
        $this->assertSame('title2', $column2->getTitle());
        $this->assertSame(false, $column2->isSortable());
        $this->assertSame('', $column2->getClassAttribute());
    }

    public function testAddColumnDuplicateId()
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );
        $grid->addColumn('columnId1', 'sourceColumnName1', 'title1');

        $this->expectException(DuplicateColumnIdException::class);
        $grid->addColumn('columnId1', 'sourceColumnName2', 'title2');
    }

    public function testEnablePaging()
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );
        $grid->enablePaging();
        $this->assertTrue($grid->isEnabledPaging());
    }

    public function testEnablePagingDefaultDisable()
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );
        $this->assertFalse($grid->isEnabledPaging());
    }

    public function testSetDefaultOrder()
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );

        $grid->setDefaultOrder('columnId1', DataSourceInterface::ORDER_DESC);
        $this->assertSame('-columnId1', $grid->getOrderSourceColumnNameWithDirection());

        $grid->setDefaultOrder('columnId2', DataSourceInterface::ORDER_ASC);
        $this->assertSame('columnId2', $grid->getOrderSourceColumnNameWithDirection());
    }

    public function testSetDefaultOrderWithRequest()
    {
        $getParameters = [
            Grid::GET_PARAMETER => [
                'gridId' => [
                    'order' => '-request',
                ],
            ],
        ];

        $request = new Request($getParameters);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );

        $grid->setDefaultOrder('default', DataSourceInterface::ORDER_ASC);
        $this->assertSame('-request', $grid->getOrderSourceColumnNameWithDirection());
    }

    public function testCreateView()
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->getMockBuilder(DataSourceInterface::class)->getMock();
        $dataSourceMock->expects($this->never())->method('getTotalRowsCount');
        $dataSourceMock->expects($this->once())->method('getPaginatedRows')
            ->willReturn(new PaginationResult(1, 1, 0, []));
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );
        $grid->createView();
    }

    public function testCreateViewWithPaging()
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->getMockBuilder(DataSourceInterface::class)->getMock();
        $dataSourceMock->expects($this->once())->method('getTotalRowsCount')->willReturn(0);
        $dataSourceMock->expects($this->once())->method('getPaginatedRows')
            ->willReturn(new PaginationResult(1, 1, 0, []));
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );
        $grid->enablePaging();
        $grid->createView();
    }

    public function testEnableDragAndDrop()
    {
        $entityClass = 'Path\To\Entity\Class';

        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );

        $this->assertFalse($grid->isDragAndDrop());
        $grid->enableDragAndDrop($entityClass);
        $this->assertTrue($grid->isDragAndDrop());
    }

    public function testReorderColumns(): void
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $twigMock = $this->createMock(Environment::class);
        $routerMock = $this->createMock(Router::class);
        $routeCsrfProtectorMock = $this->createMock(RouteCsrfProtector::class);
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $securityMock = $this->createMock(Security::class);

        $grid = new Grid(
            'gridId',
            Roles::ROLE_ALL,
            $dataSourceMock,
            $requestStack,
            $routerMock,
            $routeCsrfProtectorMock,
            $twigMock,
            $securityMock,
        );

        $column1 = $grid->addColumn('columnId1', 'sourceColumnName1', 'title1');
        $column2 = $grid->addColumn('columnId2', 'sourceColumnName2', 'title2');
        $column3 = $grid->addColumn('columnId3', 'sourceColumnName3', 'title3');

        $grid->reorderColumns(['columnId2', 'columnId1']);

        $expectedColumns = [
            'columnId2' => $column2,
            'columnId1' => $column1,
            'columnId3' => $column3,
        ];

        $this->assertSame($expectedColumns, $grid->getColumnsById());
    }
}
