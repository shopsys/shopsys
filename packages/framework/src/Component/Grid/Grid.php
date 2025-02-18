<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException;
use Shopsys\FrameworkBundle\Component\Grid\Exception\EmptyGridIdException;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class Grid
{
    public const string GET_PARAMETER = 'g';
    protected const string DEFAULT_VIEW_THEME = '@ShopsysFramework/Admin/Grid/Grid.html.twig';
    protected const int DEFAULT_LIMIT = 30;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\Column[]
     */
    protected array $columnsById = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\ActionColumn[]
     */
    protected array $actionColumns = [];

    protected bool $enablePaging = false;

    protected bool $enableSelecting = false;

    /**
     * @var int[]
     */
    protected array $allowedLimits = [30, 100, 200, 500];

    protected int $limit;

    protected bool $isLimitFromRequest = false;

    protected int $page = 1;

    protected ?int $totalCount = null;

    protected ?int $pageCount = null;

    protected ?string $orderSourceColumnName = null;

    protected string $orderDirection = DataSourceInterface::ORDER_DESC;

    protected bool $isOrderFromRequest = false;

    /**
     * @var mixed[]
     */
    protected array $rows = [];

    protected string $actionColumnClassAttribute = '';

    protected ?GridInlineEditInterface $inlineEditService = null;

    protected ?string $orderingEntityClass = null;

    protected PaginationResult $paginationResults;

    /**
     * @var string|string[]|null
     */
    protected string|array|null $viewTheme = null;

    /**
     * @var mixed[]
     */
    protected array $viewTemplateParameters = [];

    /**
     * @var int[]
     */
    protected array $selectedRowIds = [];

    protected bool $multipleDragAndDrop = false;

    /**
     * @param string $id
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $dataSource
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector
     * @param \Twig\Environment $twig
     */
    public function __construct(
        protected readonly string $id,
        protected readonly DataSourceInterface $dataSource,
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router,
        protected readonly RouteCsrfProtector $routeCsrfProtector,
        protected readonly Environment $twig,
    ) {
        if ($id === '') {
            $message = 'Grid id cannot be empty.';

            throw new EmptyGridIdException($message);
        }

        $this->limit = static::DEFAULT_LIMIT;
        $this->viewTheme = static::DEFAULT_VIEW_THEME;

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
                'Duplicate column id "' . $id . '" in grid "' . $this->id . '"',
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
     * @param array $bindingRouteParams
     * @param array $additionalRouteParams
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function addActionColumn(
        string $type,
        string $name,
        string $route,
        array $bindingRouteParams = [],
        array $additionalRouteParams = [],
    ): ActionColumn {
        $actionColumn = new ActionColumn(
            $this->router,
            $this->routeCsrfProtector,
            $type,
            $name,
            $route,
            $bindingRouteParams,
            $additionalRouteParams,
            $this,
        );
        $this->actionColumns[] = $actionColumn;

        return $actionColumn;
    }

    /**
     * @param string $route
     * @param array $bindingRouteParams
     * @param array $additionalRouteParams
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function addEditActionColumn(
        string $route,
        array $bindingRouteParams = [],
        array $additionalRouteParams = [],
    ): ActionColumn {
        return $this->addActionColumn(
            ActionColumn::TYPE_EDIT,
            t('Edit'),
            $route,
            $bindingRouteParams,
            $additionalRouteParams,
        );
    }

    /**
     * @param string $route
     * @param array $bindingRouteParams
     * @param array $additionalRouteParams
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function addDeleteActionColumn(
        string $route,
        array $bindingRouteParams = [],
        array $additionalRouteParams = [],
    ): ActionColumn {
        return $this->addActionColumn(
            ActionColumn::TYPE_DELETE,
            t('Delete'),
            $route,
            $bindingRouteParams,
            $additionalRouteParams,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface $inlineEditService
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
     * @return \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface|null
     */
    public function getInlineEditService(): ?GridInlineEditInterface
    {
        return $this->inlineEditService;
    }

    /**
     * @param array $row
     * @return mixed
     */
    public function getRowId(array $row): mixed
    {
        return $this->getValueFromRowBySourceColumnName($row, $this->dataSource->getRowIdSourceColumnName());
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
     * @param array $viewParameters
     */
    public function setTheme(array|string $viewTheme, array $viewParameters = []): void
    {
        $this->viewTheme = $viewTheme;
        $this->viewTemplateParameters = $viewParameters;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView
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
     * @param int|string $rowId
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView
     */
    public function createViewWithOneRow(int|string $rowId): GridView
    {
        $gridView = $this->createViewWithoutRows();
        $this->loadRowsWithOneRow($rowId);

        return $gridView;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView
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
            $this->viewTemplateParameters,
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
     * @return array
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
     * @param array $row
     * @return bool
     */
    public function isRowSelected(array $row): bool
    {
        $rowId = $this->getRowId($row);

        return in_array($rowId, $this->selectedRowIds, true);
    }

    /**
     * @return array
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
     * @return array
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
     * @return int
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
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
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
        $queryData = $this->requestStack->getMainRequest()->query->all(self::GET_PARAMETER);

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

        $requestData = $this->requestStack->getMainRequest()->request->all(self::GET_PARAMETER);

        if (!array_key_exists($this->id, $requestData)) {
            return;
        }

        $gridRequestData = $requestData[$this->id];

        if (array_key_exists('selectedRowIds', $gridRequestData) && is_array($gridRequestData['selectedRowIds'])) {
            $this->selectedRowIds = array_map('json_decode', $gridRequestData['selectedRowIds']);
        }
    }

    /**
     * @param array|string|null $removeParameters
     * @return array
     */
    public function getGridParameters(array|string|null $removeParameters = []): array
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
     * @return array
     */
    public function getUrlGridParameters(
        array|string|null $parameters = null,
        array|string|null $removeParameters = null,
    ): array {
        $gridParameters = array_replace_recursive(
            $this->getGridParameters($removeParameters),
            (array)$parameters,
        );

        return [self::GET_PARAMETER => [$this->getId() => $gridParameters]];
    }

    /**
     * @param array|string|null $parameters
     * @param array|string|null $removeParameters
     * @return array
     */
    public function getUrlParameters(
        array|string|null $parameters = null,
        array|string|null $removeParameters = null,
    ): array {
        return array_replace_recursive(
            $this->requestStack->getMainRequest()->query->all(),
            $this->requestStack->getMainRequest()->attributes->get('_route_params'),
            $this->getUrlGridParameters($parameters, $removeParameters),
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
            $orderDirection = DataSourceInterface::ORDER_DESC;
        }

        $this->paginationResults = $this->dataSource->getPaginatedRows(
            $this->enablePaging ? $this->limit : null,
            $this->page,
            $orderSourceColumnName,
            $orderDirection,
        );

        $this->rows = $this->paginationResults->getResults();
    }

    /**
     * @param int|string $rowId
     */
    protected function loadRowsWithOneRow(int|string $rowId): void
    {
        $this->rows = [$this->dataSource->getOneRow($rowId)];
    }

    protected function executeTotalQuery(): void
    {
        $this->totalCount = $this->dataSource->getTotalRowsCount();
        $this->pageCount = (int)max(ceil($this->totalCount / $this->limit), 1);
        $this->page = min($this->page, $this->pageCount);
    }

    /**
     * @param array $row
     * @param string $sourceColumnName
     * @return mixed
     */
    public function getValueFromRowBySourceColumnName(array $row, string $sourceColumnName): mixed
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
     * @param string $entityClass
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
     * @return string|null
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

    /**
     * @param string[] $orderedColumnIds
     */
    public function reorderColumns(array $orderedColumnIds): void
    {
        $orderedColumns = [];

        foreach ($orderedColumnIds as $columnId) {
            $orderedColumns[$columnId] = $this->columnsById[$columnId];
        }

        $this->columnsById = [...$orderedColumns, ...$this->columnsById];
    }
}
