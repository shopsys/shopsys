<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig_Environment;

class Grid
{
    const GET_PARAMETER = 'g';
    const DEFAULT_VIEW_THEME = '@ShopsysFramework/Admin/Grid/Grid.html.twig';
    const DEFAULT_LIMIT = 30;

    /**
     * @var string
     */
    private $id;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\Column[]
     */
    private $columnsById = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\ActionColumn[]
     */
    private $actionColumns = [];

    /**
     * @var bool
     */
    private $enablePaging = false;

    /**
     * @var bool
     */
    private $enableSelecting = false;

    /**
     * @var array
     */
    private $allowedLimits = [30, 100, 200, 500];

    /**
     * @var int
     */
    private $limit;

    /**
     * @var bool
     */
    private $isLimitFromRequest = false;

    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var int|null
     */
    private $totalCount;

    /**
     * @var int|null
     */
    private $pageCount;

    /**
     * @var string|null
     */
    private $orderSourceColumnName;

    /**
     * @var string|null
     */
    private $orderDirection;

    /**
     * @var bool
     */
    private $isOrderFromRequest = false;

    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector
     */
    private $routeCsrfProtector;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    private $dataSource;

    /**
     * @var string
     */
    private $actionColumnClassAttribute = '';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface|null
     */
    private $inlineEditService;

    /**
     * @var string|null
     */
    private $orderingEntityClass;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    private $paginationResults;

    /**
     * @var string|string[]|null
     */
    private $viewTheme;

    /**
     * @var array
     */
    private $viewTemplateParameters;

    /**
     * @var array
     */
    private $selectedRowIds;

    /**
     * @var bool
     */
    private $multipleDragAndDrop;

    /**
     * @param string $id
     */
    public function __construct(
        $id,
        DataSourceInterface $dataSource,
        RequestStack $requestStack,
        RouterInterface $router,
        RouteCsrfProtector $routeCsrfProtector,
        Twig_Environment $twig
    ) {
        if (empty($id)) {
            $message = 'Grid id cannot be empty.';
            throw new \Shopsys\FrameworkBundle\Component\Grid\Exception\EmptyGridIdException($message);
        }

        $this->id = $id;
        $this->dataSource = $dataSource;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->routeCsrfProtector = $routeCsrfProtector;
        $this->twig = $twig;

        $this->limit = self::DEFAULT_LIMIT;
        $this->page = 1;

        $this->viewTheme = self::DEFAULT_VIEW_THEME;
        $this->viewTemplateParameters = [];

        $this->selectedRowIds = [];
        $this->multipleDragAndDrop = false;

        $this->loadFromRequest();
    }

    /**
     * @param string $id
     * @param string $sourceColumnName
     * @param string $title
     * @param bool $sortable
     */
    public function addColumn($id, $sourceColumnName, $title, $sortable = false): \Shopsys\FrameworkBundle\Component\Grid\Column
    {
        if (array_key_exists($id, $this->columnsById)) {
            throw new \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException(
                'Duplicate column id "' . $id . '" in grid "' . $this->id . '"'
            );
        }
        $column = new Column($id, $sourceColumnName, $title, $sortable);
        $this->columnsById[$id] = $column;
        return $column;
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $route
     */
    public function addActionColumn(
        $type,
        $name,
        $route,
        array $bindingRouteParams = [],
        array $additionalRouteParams = []
    ): \Shopsys\FrameworkBundle\Component\Grid\ActionColumn {
        $actionColumn = new ActionColumn(
            $this->router,
            $this->routeCsrfProtector,
            $type,
            $name,
            $route,
            $bindingRouteParams,
            $additionalRouteParams
        );
        $this->actionColumns[] = $actionColumn;

        return $actionColumn;
    }

    /**
     * @param string $route
     */
    public function addEditActionColumn($route, array $bindingRouteParams = [], array $additionalRouteParams = []): \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
    {
        return $this->addActionColumn(ActionColumn::TYPE_EDIT, t('Edit'), $route, $bindingRouteParams, $additionalRouteParams);
    }

    /**
     * @param string $route
     */
    public function addDeleteActionColumn($route, array $bindingRouteParams = [], array $additionalRouteParams = []): \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
    {
        return $this->addActionColumn(ActionColumn::TYPE_DELETE, t('Delete'), $route, $bindingRouteParams, $additionalRouteParams);
    }

    public function setInlineEditService(GridInlineEditInterface $inlineEditService)
    {
        $this->inlineEditService = $inlineEditService;
    }

    public function isInlineEdit(): bool
    {
        return $this->inlineEditService !== null;
    }

    public function getInlineEditService(): ?\Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface
    {
        return $this->inlineEditService;
    }

    /**
     * @param array $row
     * @return mixed
     */
    public function getRowId($row)
    {
        return self::getValueFromRowBySourceColumnName($row, $this->dataSource->getRowIdSourceColumnName());
    }

    /**
     * @param string $classAttribute
     */
    public function setActionColumnClassAttribute($classAttribute)
    {
        $this->actionColumnClassAttribute = $classAttribute;
    }

    /**
     * @param string|string[] $viewTheme
     */
    public function setTheme($viewTheme, array $viewParameters = [])
    {
        $this->viewTheme = $viewTheme;
        $this->viewTemplateParameters = $viewParameters;
    }

    public function createView(): \Shopsys\FrameworkBundle\Component\Grid\GridView
    {
        $gridView = $this->createViewWithoutRows();
        if ($this->isEnabledPaging()) {
            $this->executeTotalQuery();
        }
        $this->loadRows();

        return $gridView;
    }

    /**
     * @param int $rowId
     */
    public function createViewWithOneRow($rowId): \Shopsys\FrameworkBundle\Component\Grid\GridView
    {
        $gridView = $this->createViewWithoutRows();
        $this->loadRowsWithOneRow($rowId);

        return $gridView;
    }

    public function createViewWithoutRows(): \Shopsys\FrameworkBundle\Component\Grid\GridView
    {
        $this->rows = [];
        $gridView = new GridView(
            $this,
            $this->requestStack,
            $this->router,
            $this->twig,
            $this->viewTheme,
            $this->viewTemplateParameters
        );

        return $gridView;
    }

    public function enablePaging()
    {
        $this->enablePaging = true;
    }

    public function enableSelecting()
    {
        $this->enableSelecting = true;
    }

    /**
     * @param int $limit
     */
    public function setDefaultLimit($limit)
    {
        if (!$this->isLimitFromRequest) {
            $this->setLimit((int)$limit);
        }
    }

    /**
     * @param string $columnId
     * @param string $direction
     */
    public function setDefaultOrder($columnId, $direction = DataSourceInterface::ORDER_ASC)
    {
        if (!$this->isOrderFromRequest) {
            $prefix = $direction === DataSourceInterface::ORDER_DESC ? '-' : '';
            $this->setOrderingByOrderString($prefix . $columnId);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Column[]
     */
    public function getColumnsById(): array
    {
        return $this->columnsById;
    }

    /**
     * @param string $columnId
     */
    public function existsColumn($columnId): bool
    {
        return array_key_exists($columnId, $this->columnsById);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn[]
     */
    public function getActionColumns(): array
    {
        return $this->actionColumns;
    }
    
    public function getRows()
    {
        return $this->rows;
    }

    public function isEnabledPaging(): bool
    {
        return $this->enablePaging;
    }

    public function isEnabledSelecting(): bool
    {
        return $this->enableSelecting;
    }

    public function isRowSelected(array $row): bool
    {
        $rowId = $this->getRowId($row);
        return in_array($rowId, $this->selectedRowIds, true);
    }
    
    public function getSelectedRowIds()
    {
        return $this->selectedRowIds;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    private function setLimit($limit)
    {
        if (in_array($limit, $this->allowedLimits, true)) {
            $this->limit = $limit;
        }
    }
    
    public function getAllowedLimits()
    {
        return $this->allowedLimits;
    }

    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function getOrderSourceColumnName(): ?string
    {
        return $this->orderSourceColumnName;
    }

    public function getOrderSourceColumnNameWithDirection(): ?string
    {
        $prefix = '';
        if ($this->getOrderDirection() === DataSourceInterface::ORDER_DESC) {
            $prefix = '-';
        }

        return $prefix . $this->getOrderSourceColumnName();
    }

    public function getOrderDirection(): ?string
    {
        return $this->orderDirection;
    }

    public function getActionColumnClassAttribute(): string
    {
        return $this->actionColumnClassAttribute;
    }

    public function getPaginationResults(): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
    {
        return $this->paginationResults;
    }

    /**
     * @param string $orderString
     */
    private function setOrderingByOrderString($orderString)
    {
        if (substr($orderString, 0, 1) === '-') {
            $this->orderDirection = DataSourceInterface::ORDER_DESC;
        } else {
            $this->orderDirection = DataSourceInterface::ORDER_ASC;
        }
        $this->orderSourceColumnName = trim($orderString, '-');
    }

    private function loadFromRequest()
    {
        $queryData = $this->requestStack->getMasterRequest()->query->get(self::GET_PARAMETER, []);
        if (array_key_exists($this->id, $queryData)) {
            $gridQueryData = $queryData[$this->id];
            if (array_key_exists('limit', $gridQueryData)) {
                $this->setLimit((int)trim($gridQueryData['limit']));
                $this->isLimitFromRequest = true;
            }
            if (array_key_exists('page', $gridQueryData)) {
                $this->page = max((int)trim($gridQueryData['page']), 1);
            }
            if (array_key_exists('order', $gridQueryData)) {
                $this->setOrderingByOrderString(trim($gridQueryData['order']));
                $this->isOrderFromRequest = true;
            }
        }
        $requestData = $this->requestStack->getMasterRequest()->request->get(self::GET_PARAMETER, []);
        if (array_key_exists($this->id, $requestData)) {
            $gridRequestData = $requestData[$this->id];
            if (array_key_exists('selectedRowIds', $gridRequestData) && is_array($gridRequestData['selectedRowIds'])) {
                $this->selectedRowIds = array_map('json_decode', $gridRequestData['selectedRowIds']);
            }
        }
    }

    /**
     * @param array|string $removeParameters
     */
    public function getGridParameters($removeParameters = []): array
    {
        $gridParameters = [];
        if ($this->isEnabledPaging()) {
            $gridParameters['limit'] = $this->getLimit();
            if ($this->getPage() > 1) {
                $gridParameters['page'] = $this->getPage();
            }
        }
        if ($this->getOrderSourceColumnName() !== null) {
            $gridParameters['order'] = $this->getOrderSourceColumnNameWithDirection();
        }

        foreach ((array)$removeParameters as $parameterToRemove) {
            if (array_key_exists($parameterToRemove, $gridParameters)) {
                unset($gridParameters[$parameterToRemove]);
            }
        }

        return $gridParameters;
    }

    /**
     * @param array|string|null $parameters
     * @param array|string|null $removeParameters
     */
    public function getUrlGridParameters($parameters = null, $removeParameters = null): array
    {
        $gridParameters = array_replace_recursive(
            $this->getGridParameters($removeParameters),
            (array)$parameters
        );

        return [self::GET_PARAMETER => [$this->getId() => $gridParameters]];
    }

    /**
     * @param array|string|null $parameters
     * @param array|string|null $removeParameters
     */
    public function getUrlParameters($parameters = null, $removeParameters = null): array
    {
        return array_replace_recursive(
            $this->requestStack->getMasterRequest()->query->all(),
            $this->requestStack->getMasterRequest()->attributes->get('_route_params'),
            $this->getUrlGridParameters($parameters, $removeParameters)
        );
    }

    private function loadRows()
    {
        if (array_key_exists($this->orderSourceColumnName, $this->columnsById)
            && $this->columnsById[$this->orderSourceColumnName]->isSortable()
        ) {
            $orderSourceColumnName = $this->columnsById[$this->orderSourceColumnName]->getOrderSourceColumnName();
        } else {
            $orderSourceColumnName = null;
        }

        $orderDirection = $this->orderDirection;

        if ($this->isDragAndDrop()) {
            $orderSourceColumnName = null;
            $orderDirection = null;
        }

        $this->paginationResults = $this->dataSource->getPaginatedRows(
            $this->enablePaging ? $this->limit : null,
            $this->page,
            $orderSourceColumnName,
            $orderDirection
        );

        $this->rows = $this->paginationResults->getResults();
    }

    /**
     * @param int $rowId
     */
    private function loadRowsWithOneRow($rowId)
    {
        $this->rows = [$this->dataSource->getOneRow($rowId)];
    }

    private function executeTotalQuery()
    {
        $this->totalCount = $this->dataSource->getTotalRowsCount();
        $this->pageCount = max(ceil($this->totalCount / $this->limit), 1);
        $this->page = min($this->page, $this->pageCount);
    }

    /**
     * @param string $sourceColumnName
     * @return mixed
     */
    public static function getValueFromRowBySourceColumnName(array $row, $sourceColumnName)
    {
        $sourceColumnNameParts = explode('.', $sourceColumnName);

        if (count($sourceColumnNameParts) === 1) {
            return $row[$sourceColumnNameParts[0]];
        } elseif (count($sourceColumnNameParts) === 2) {
            if (array_key_exists($sourceColumnNameParts[0], $row)
                && array_key_exists($sourceColumnNameParts[1], $row[$sourceColumnNameParts[0]])
            ) {
                return $row[$sourceColumnNameParts[0]][$sourceColumnNameParts[1]];
            } elseif (array_key_exists($sourceColumnNameParts[1], $row)) {
                return $row[$sourceColumnNameParts[1]];
            } else {
                return $row[$sourceColumnName];
            }
        }

        return $row[$sourceColumnName];
    }

    /**
     * @param string $entityClass
     */
    public function enableDragAndDrop($entityClass)
    {
        $this->orderingEntityClass = $entityClass;
    }

    public function enableMultipleDragAndDrop()
    {
        $this->multipleDragAndDrop = true;
    }

    public function isDragAndDrop(): bool
    {
        return $this->orderingEntityClass !== null;
    }

    public function getOrderingEntityClass(): ?string
    {
        return $this->orderingEntityClass;
    }

    public function isMultipleDragAndDrop(): bool
    {
        return $this->multipleDragAndDrop;
    }
}
