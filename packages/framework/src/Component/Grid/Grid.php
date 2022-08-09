<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException;
use Shopsys\FrameworkBundle\Component\Grid\Exception\EmptyGridIdException;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * @template T of array<string, mixed>
 */
class Grid
{
    public const GET_PARAMETER = 'g';
    protected const DEFAULT_VIEW_THEME = '@ShopsysFramework/Admin/Grid/Grid.html.twig';
    protected const DEFAULT_LIMIT = 30;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\Column[]
     */
    protected $columnsById = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\ActionColumn[]
     */
    protected $actionColumns = [];

    /**
     * @var bool
     */
    protected $enablePaging = false;

    /**
     * @var bool
     */
    protected $enableSelecting = false;

    /**
     * @var int[]
     */
    protected $allowedLimits = [30, 100, 200, 500];

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var bool
     */
    protected $isLimitFromRequest = false;

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var int|null
     */
    protected $totalCount;

    /**
     * @var int|null
     */
    protected $pageCount;

    /**
     * @var string|null
     */
    protected $orderSourceColumnName;

    /**
     * @var string|null
     */
    protected $orderDirection;

    /**
     * @var bool
     */
    protected $isOrderFromRequest = false;

    /**
     * @var array<array<string, mixed>>
     */
    protected $rows = [];

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector
     */
    protected $routeCsrfProtector;

    /**
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface<T>
     */
    protected $dataSource;

    /**
     * @var string
     */
    protected $actionColumnClassAttribute = '';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface<T>|null
     */
    protected $inlineEditService;

    /**
     * @var class-string|null
     */
    protected $orderingEntityClass;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    protected $paginationResults;

    /**
     * @var string|string[]|null
     */
    protected $viewTheme;

    /**
     * @var array<string, mixed>
     */
    protected $viewTemplateParameters;

    /**
     * @var array<int|string>
     */
    protected $selectedRowIds;

    /**
     * @var bool
     */
    protected $multipleDragAndDrop;

    /**
     * @param string $id
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface<T> $dataSource
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector
     * @param \Twig\Environment $twig
     */
    public function __construct(
        string $id,
        DataSourceInterface $dataSource,
        RequestStack $requestStack,
        RouterInterface $router,
        RouteCsrfProtector $routeCsrfProtector,
        Environment $twig
    ) {
        if ($id === '') {
            throw new EmptyGridIdException('Grid id cannot be empty.');
        }

        $this->id = $id;
        $this->dataSource = $dataSource;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->routeCsrfProtector = $routeCsrfProtector;
        $this->twig = $twig;

        $this->limit = static::DEFAULT_LIMIT;
        $this->page = 1;

        $this->viewTheme = static::DEFAULT_VIEW_THEME;
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
     * @return \Shopsys\FrameworkBundle\Component\Grid\Column
     */
    public function addColumn(string $id, string $sourceColumnName, string $title, bool $sortable = false): Column
    {
        if (array_key_exists($id, $this->columnsById)) {
            throw new DuplicateColumnIdException(
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
     * @param array<string, string> $bindingRouteParams
     * @param array<string, string|int|float|bool|null> $additionalRouteParams
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function addActionColumn(
        string $type,
        string $name,
        string $route,
        array $bindingRouteParams = [],
        array $additionalRouteParams = []
    ): ActionColumn {
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
     * @param array<string, string> $bindingRouteParams
     * @param array<string, string|int|float|bool|null> $additionalRouteParams
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function addEditActionColumn(string $route, array $bindingRouteParams = [], array $additionalRouteParams = []): ActionColumn
    {
        return $this->addActionColumn(
            ActionColumn::TYPE_EDIT,
            t('Edit'),
            $route,
            $bindingRouteParams,
            $additionalRouteParams
        );
    }

    /**
     * @param string $route
     * @param array<string, string> $bindingRouteParams
     * @param array<string, string|int|float|bool|null> $additionalRouteParams
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function addDeleteActionColumn(string $route, array $bindingRouteParams = [], array $additionalRouteParams = []): ActionColumn
    {
        return $this->addActionColumn(
            ActionColumn::TYPE_DELETE,
            t('Delete'),
            $route,
            $bindingRouteParams,
            $additionalRouteParams
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface<T> $inlineEditService
     */
    public function setInlineEditService(GridInlineEditInterface $inlineEditService): void
    {
        $this->inlineEditService = $inlineEditService;
    }

    /**
     * @return bool
     */
    public function isInlineEdit(): bool
    {
        return $this->inlineEditService !== null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface<T>|null
     */
    public function getInlineEditService(): ?GridInlineEditInterface
    {
        return $this->inlineEditService;
    }

    /**
     * @param array<string, mixed> $row
     * @return mixed
     */
    public function getRowId(array $row): mixed
    {
        return self::getValueFromRowBySourceColumnName($row, $this->dataSource->getRowIdSourceColumnName());
    }

    /**
     * @param string $classAttribute
     */
    public function setActionColumnClassAttribute(string $classAttribute): void
    {
        $this->actionColumnClassAttribute = $classAttribute;
    }

    /**
     * @param string|string[] $viewTheme
     * @param array<string, mixed> $viewParameters
     */
    public function setTheme(string|array $viewTheme, array $viewParameters = []): void
    {
        $this->viewTheme = $viewTheme;
        $this->viewTemplateParameters = $viewParameters;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView<T>
     */
    public function createView(): GridView
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
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView<T>
     */
    public function createViewWithOneRow(int $rowId): GridView
    {
        $gridView = $this->createViewWithoutRows();
        $this->loadRowsWithOneRow($rowId);

        return $gridView;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView<T>
     */
    public function createViewWithoutRows(): GridView
    {
        $this->rows = [];
        return new GridView(
            $this,
            $this->requestStack,
            $this->router,
            $this->twig,
            $this->viewTheme,
            $this->viewTemplateParameters
        );
    }

    public function enablePaging(): void
    {
        $this->enablePaging = true;
    }

    public function enableSelecting(): void
    {
        $this->enableSelecting = true;
    }

    /**
     * @param int $limit
     */
    public function setDefaultLimit(int $limit): void
    {
        if (!$this->isLimitFromRequest) {
            $this->setLimit((int)$limit);
        }
    }

    /**
     * @param string $columnId
     * @param string $direction
     */
    public function setDefaultOrder(string $columnId, string $direction = DataSourceInterface::ORDER_ASC): void
    {
        if (!$this->isOrderFromRequest) {
            $prefix = $direction === DataSourceInterface::ORDER_DESC ? '-' : '';
            $this->setOrderingByOrderString($prefix . $columnId);
        }
    }

    /**
     * @return string
     */
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
     * @return bool
     */
    public function existsColumn(string $columnId): bool
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

    /**
     * @return array<array<string, mixed>>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return bool
     */
    public function isEnabledPaging(): bool
    {
        return $this->enablePaging;
    }

    /**
     * @return bool
     */
    public function isEnabledSelecting(): bool
    {
        return $this->enableSelecting;
    }

    /**
     * @param array<string, mixed> $row
     * @return bool
     */
    public function isRowSelected(array $row): bool
    {
        $rowId = $this->getRowId($row);
        return in_array($rowId, $this->selectedRowIds, true);
    }

    /**
     * @return int[]|string[]
     */
    public function getSelectedRowIds(): array
    {
        return $this->selectedRowIds;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    protected function setLimit(int $limit): void
    {
        if (in_array($limit, $this->allowedLimits, true)) {
            $this->limit = $limit;
        }
    }

    /**
     * @return int[]
     */
    public function getAllowedLimits(): array
    {
        return $this->allowedLimits;
    }

    /**
     * @return int|null
     */
    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int|null
     */
    public function getPageCount(): ?int
    {
        return $this->pageCount;
    }

    /**
     * @return string|null
     */
    public function getOrderSourceColumnName(): ?string
    {
        return $this->orderSourceColumnName;
    }

    /**
     * @return string|null
     */
    public function getOrderSourceColumnNameWithDirection(): ?string
    {
        $prefix = '';
        if ($this->getOrderDirection() === DataSourceInterface::ORDER_DESC) {
            $prefix = '-';
        }

        return $prefix . $this->getOrderSourceColumnName();
    }

    /**
     * @return string|null
     */
    public function getOrderDirection(): ?string
    {
        return $this->orderDirection;
    }

    /**
     * @return string
     */
    public function getActionColumnClassAttribute(): string
    {
        return $this->actionColumnClassAttribute;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    public function getPaginationResults(): PaginationResult
    {
        return $this->paginationResults;
    }

    /**
     * @param string $orderString
     */
    protected function setOrderingByOrderString(string $orderString): void
    {
        if (substr($orderString, 0, 1) === '-') {
            $this->orderDirection = DataSourceInterface::ORDER_DESC;
        } else {
            $this->orderDirection = DataSourceInterface::ORDER_ASC;
        }
        $this->orderSourceColumnName = trim($orderString, '-');
    }

    protected function loadFromRequest(): void
    {
        /** @var array<string, mixed> $queryData */
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
        if (!array_key_exists($this->id, $requestData)) {
            return;
        }

        $gridRequestData = $requestData[$this->id];
        if (array_key_exists('selectedRowIds', $gridRequestData) && is_array($gridRequestData['selectedRowIds'])) {
            $this->selectedRowIds = array_map('json_decode', $gridRequestData['selectedRowIds']);
        }
    }

    /**
     * @param string|string[]|null $removeParameters
     * @return int[]|string[]|null[]
     */
    public function getGridParameters(string|array|null $removeParameters = []): array
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
     * @param string|string[]|null $parameters
     * @param string|string[]|null $removeParameters
     * @return array<string, mixed>
     */
    public function getUrlGridParameters(string|array|null $parameters = null, string|array|null $removeParameters = null): array
    {
        $gridParameters = array_replace_recursive(
            $this->getGridParameters($removeParameters),
            (array)$parameters
        );

        return [self::GET_PARAMETER => [$this->getId() => $gridParameters]];
    }

    /**
     * @param string|string[]|null $parameters
     * @param string|string[]|null $removeParameters
     * @return array<string, mixed>
     */
    public function getUrlParameters(string|array|null $parameters = null, string|array|null $removeParameters = null): array
    {
        return array_replace_recursive(
            $this->requestStack->getMasterRequest()->query->all(),
            $this->requestStack->getMasterRequest()->attributes->get('_route_params'),
            $this->getUrlGridParameters($parameters, $removeParameters)
        );
    }

    protected function loadRows(): void
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
    protected function loadRowsWithOneRow(int $rowId): void
    {
        $this->rows = [$this->dataSource->getOneRow($rowId)];
    }

    protected function executeTotalQuery(): void
    {
        $this->totalCount = $this->dataSource->getTotalRowsCount();
        $this->pageCount = max(ceil($this->totalCount / $this->limit), 1);
        $this->page = min($this->page, $this->pageCount);
    }

    /**
     * @param array<string, mixed> $row
     * @param string $sourceColumnName
     * @return mixed
     */
    public static function getValueFromRowBySourceColumnName(array $row, string $sourceColumnName): mixed
    {
        $sourceColumnNameParts = explode('.', $sourceColumnName);

        if (count($sourceColumnNameParts) === 1) {
            return $row[$sourceColumnNameParts[0]];
        }

        if (count($sourceColumnNameParts) === 2) {
            if (array_key_exists($sourceColumnNameParts[0], $row)
                && array_key_exists($sourceColumnNameParts[1], $row[$sourceColumnNameParts[0]])
            ) {
                return $row[$sourceColumnNameParts[0]][$sourceColumnNameParts[1]];
            }

            if (array_key_exists($sourceColumnNameParts[1], $row)) {
                return $row[$sourceColumnNameParts[1]];
            }
            return $row[$sourceColumnName];
        }

        return $row[$sourceColumnName];
    }

    /**
     * @param class-string $entityClass
     */
    public function enableDragAndDrop(string $entityClass): void
    {
        $this->orderingEntityClass = $entityClass;
    }

    public function enableMultipleDragAndDrop(): void
    {
        $this->multipleDragAndDrop = true;
    }

    /**
     * @return bool
     */
    public function isDragAndDrop(): bool
    {
        return $this->orderingEntityClass !== null;
    }

    /**
     * @return class-string|null
     */
    public function getOrderingEntityClass(): ?string
    {
        return $this->orderingEntityClass;
    }

    /**
     * @return bool
     */
    public function isMultipleDragAndDrop(): bool
    {
        return $this->multipleDragAndDrop;
    }
}
