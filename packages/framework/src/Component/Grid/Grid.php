<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException;
use Shopsys\FrameworkBundle\Component\Grid\Exception\EmptyGridIdException;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class Grid
{
    public const string GET_PARAMETER = 'g';
    protected const string DEFAULT_VIEW_THEME = '@ShopsysAdministration/datagrid/grid.html.twig';
    protected const int DEFAULT_LIMIT = 30;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\Column[]
     */
    protected array $columnsById = [];

    /**
     * @var array<\Shopsys\FrameworkBundle\Component\Grid\ActionColumn|\Shopsys\FrameworkBundle\Component\Grid\GridRowActionInterface>
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
     * @var array<int, array<string, mixed>>
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
     * @var array<string, mixed>
     */
    protected array $viewTemplateParameters = [];

    /**
     * @var int[]
     */
    protected array $selectedRowIds = [];

    protected bool $multipleDragAndDrop = false;

    protected ?string $title = null;

    /**
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\EmptyGridIdException
     */
    public function __construct(
        protected readonly string $id,
        protected readonly string $roleConstant,
        protected readonly DataSourceInterface $dataSource,
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router,
        protected readonly RouteCsrfProtector $routeCsrfProtector,
        protected readonly Environment $twig,
        protected readonly AccessCheckerInterface $accessChecker,
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
     * @param array{template?: string, help?: string }&array<string, mixed> $options
     */
    public function addColumn(
        string $id,
        string $sourceColumnName,
        string $title,
        bool $sortable = false,
        array $options = [],
    ): Column {
        if (array_key_exists($id, $this->columnsById)) {
            throw new DuplicateColumnIdException(
                'Duplicate column id "' . $id . '" in grid "' . $this->id . '"',
            );
        }
        $column = new Column($id, $sourceColumnName, $title, $sortable, $options);
        $this->columnsById[$id] = $column;

        return $column;
    }

    /**
     * @param array<string, string> $bindingRouteParams
     * @param array<string, mixed> $additionalRouteParams
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

        if ($this->accessChecker->hasAccessToRoute($route, HttpMethod::GET)) {
            $this->actionColumns[] = $actionColumn;
        }

        return $actionColumn;
    }

    /**
     * @param array<string, string> $bindingRouteParams
     * @param array<string, mixed> $additionalRouteParams
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
     * @param array<string, string> $bindingRouteParams
     * @param array<string, mixed> $additionalRouteParams
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

    public function addRowAction(GridRowActionInterface $rowAction): GridRowActionInterface
    {
        $this->actionColumns[] = $rowAction;

        return $rowAction;
    }

    public function setInlineEditService(GridInlineEditInterface $inlineEditService): void
    {
        if ($inlineEditService->canAddNewRow() === false && $this->accessChecker->canEdit($this->roleConstant) === false) {
            return;
        }

        $this->inlineEditService = $inlineEditService;
    }

    public function isInlineEdit(): bool
    {
        return $this->inlineEditService !== null;
    }

    public function getInlineEditService(): ?GridInlineEditInterface
    {
        return $this->inlineEditService;
    }

    /**
     * @param array<string, mixed> $row
     */
    public function getRowId(array $row): mixed
    {
        return $this->getValueFromRowBySourceColumnName($row, $this->dataSource->getRowIdSourceColumnName());
    }

    public function setActionColumnClassAttribute(string $classAttribute): void
    {
        $this->actionColumnClassAttribute = $classAttribute;
    }

    /**
     * @param string|string[] $viewTheme
     * @param array<string, mixed> $viewParameters
     */
    public function setTheme(array|string $viewTheme, array $viewParameters = []): void
    {
        $this->viewTheme = $viewTheme;
        $this->viewTemplateParameters = $viewParameters;
    }

    public function createView(): GridView
    {
        $gridView = $this->createViewWithoutRows();

        if ($this->isEnabledPaging()) {
            $this->executeTotalQuery();
        }
        $this->loadRows();

        return $gridView;
    }

    public function createViewWithOneRow(int|string $rowId): GridView
    {
        $gridView = $this->createViewWithoutRows();
        $this->loadRowsWithOneRow($rowId);

        return $gridView;
    }

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

    public function setDefaultLimit(int $limit): void
    {
        if (!$this->isLimitFromRequest) {
            $this->setLimit((int)$limit);
        }
    }

    public function setDefaultOrder(string $columnId, string $direction = DataSourceInterface::ORDER_ASC): void
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

    public function existsColumn(string $columnId): bool
    {
        return array_key_exists($columnId, $this->columnsById);
    }

    /**
     * @return array<\Shopsys\FrameworkBundle\Component\Grid\ActionColumn|\Shopsys\FrameworkBundle\Component\Grid\GridRowActionInterface>
     */
    public function getActionColumns(): array
    {
        return $this->actionColumns;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function isEnabledPaging(): bool
    {
        return $this->enablePaging;
    }

    public function isEnabledSelecting(): bool
    {
        return $this->enableSelecting && $this->accessChecker->canEdit($this->roleConstant);
    }

    /**
     * @param array<string, mixed> $row
     */
    public function isRowSelected(array $row): bool
    {
        $rowId = $this->getRowId($row);

        return in_array($rowId, $this->selectedRowIds, true);
    }

    /**
     * @return int[]
     */
    public function getSelectedRowIds(): array
    {
        return $this->selectedRowIds;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

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

    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageCount(): ?int
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

    public function getPaginationResults(): PaginationResult
    {
        return $this->paginationResults;
    }

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
     * @param array<string, mixed>|string|null $removeParameters
     * @return array<string, mixed>
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
     * @param array<string, mixed>|string|null $parameters
     * @param array<string, mixed>|string|null $removeParameters
     * @return array<string, mixed>
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
     * @param array<string, mixed>|string|null $parameters
     * @param array<string, mixed>|string|null $removeParameters
     * @return array<string, mixed>
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
     * @param array<string, mixed> $row
     */
    public function getValueFromRowBySourceColumnName(array $row, string $sourceColumnName): mixed
    {
        if (array_key_exists($sourceColumnName, $row)) {
            return $row[$sourceColumnName];
        }

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

    public function enableDragAndDrop(string $entityClass): void
    {
        $this->orderingEntityClass = $entityClass;
    }

    public function enableMultipleDragAndDrop(): void
    {
        $this->multipleDragAndDrop = true;
    }

    public function isDragAndDrop(): bool
    {
        return $this->orderingEntityClass !== null && $this->accessChecker->canEdit($this->roleConstant);
    }

    public function getOrderingEntityClass(): ?string
    {
        return $this->orderingEntityClass;
    }

    public function isMultipleDragAndDrop(): bool
    {
        return $this->multipleDragAndDrop && $this->accessChecker->canEdit($this->roleConstant);
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

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
