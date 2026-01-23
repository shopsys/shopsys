# Grid Data Sources

As a data source, the [Grid Component](index.md) requires an implementation of [`DataSourceInterface`]({{github.link}}/packages/framework/src/Component/Grid/DataSourceInterface.php).

## Use Factories for Creating DataSources

- [`QueryBuilderDataSourceFactory`]({{github.link}}/packages/framework/src/Component/Grid/QueryBuilderDataSourceFactory.php)
- [`QueryBuilderWithRowManipulatorDataSourceFactory`]({{github.link}}/packages/framework/src/Component/Grid/QueryBuilderWithRowManipulatorDataSourceFactory.php)
- [`ArrayDataSourceFactory`]({{github.link}}/packages/framework/src/Component/Grid/ArrayDataSourceFactory.php)
- [`ArrayWithPaginationDataSourceFactory`]({{github.link}}/packages/framework/src/Component/Grid/ArrayWithPaginationDataSourceFactory.php)
- [`MoneyConvertingDataSourceDecoratorFactory`]({{github.link}}/packages/framework/src/Component/Grid/MoneyConvertingDataSourceDecoratorFactory.php)

## [`QueryBuilderDataSource`]({{github.link}}/packages/framework/src/Component/Grid/QueryBuilderDataSource.php)

The most commonly used data source is created from Doctrine Query Builder.

### Example of usage

```php
public function __construct(
    private readonly \Doctrine\ORM\EntityManagerInterface $em,
    private readonly \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
) {
}

$queryBuilder = $this->em->createQueryBuilder();

$queryBuilder->select('p')
    ->from(Product::class, 'p');

$dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'p.id');
```

QueryBuilderDataSource contains a third default parameter `$hint` which is pre-set to `SortableNullsWalker::class` as a way to sort null values to the beginning in the case of ASC sorting, or to the end in the case of DESC sorting.
This default setting overrides the default behavior of postgreSQL and in the case of large tables can cause the query to slow down.
For this case and after considering all options, it is possible to set the hint to NULL and thus keep the default behavior of postgreSQL to speed up the query.

## [`QueryBuilderWithRowManipulatorDataSource`]({{github.link}}/packages/framework/src/Component/Grid/QueryBuilderWithRowManipulatorDataSource.php)

This data source is created from query builder as well, and on top of it, it allows to define a callback that is applied on each row so additional data can be set this way,
e.g., you can add some calculated price into the data set.

### Example of usage

```php
public function __construct(
    private readonly \Doctrine\ORM\EntityManagerInterface $em,
    private readonly \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
    private readonly \Shopsys\FrameworkBundle\Model\Localization\Localization $localization,
    private readonly \Shopsys\FrameworkBundle\Model\Transport\TransportRepository $transportRepository,
) {
}

$queryBuilder = $this->transportRepository->getQueryBuilderForAll()
    ->addSelect('tt')
    ->join('t.translations', 'tt', Join::WITH, 'tt.locale = :locale')
    ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities());

$dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
    $queryBuilder,
    't.id',
    function ($row, $results) {
        if ($this->transportsIndexedByIdLocalCache === null) {
            $this->transportsIndexedByIdLocalCache = $this->transportRepository->findAllByIdsIndexedById(array_map(fn ($result) => $result['t']['id'], $results));
        }

        $transport = $this->transportsIndexedByIdLocalCache[$row['t']['id']];
        $row['displayPrice'] = getDisplayPrice($transport);
        return $row;
    },
    SortableNullsWalker::class
);
```

As in the case of `QueryBuilderDataSource`, it is possible to change the setting of the `$hint` parameter, see [`QueryBuilderDataSource`](#querybuilderdatasource).

### Tips for optimal usage QueryBuilderDataSource or QueryBuilderWithRowManipulatorDataSource

- The query should return an array of scalar values instead of an entity if the table contains large collections of records.
- Grid with callback calls the callback for each item separately. To avoid calling SQL queries for each item, use the second parameter of the callback function in which the entire result for the bulk query is stored in the local cache. See. e.g. `Shopsys\FrameworkBundle\Controller\Admin\OrderController::getOrdersGrid()`.
- For large collections, consider turning off the $hint parameter, it may speed up the query.
- For large collections, create indexes over columns in conditions.
- For columns in conditions where the LIKE "%something%" condition is used, use a trigram index instead of default B-tree index. (CREATE INDEX example_trgm_idx ON example USING gin (text_column gin_trgm_ops);)

## [`ArrayDataSource`]({{github.link}}/packages/framework/src/Component/Grid/ArrayDataSource.php)

Data source that is created from an array. It is suitable when you need to display data that are not stored in the database.

### Example of usage

```php
public function __construct(
    private readonly \Shopsys\FrameworkBundle\Component\Domain\Domain $domain,
    private readonly \Shopsys\FrameworkBundle\Component\Grid\ArrayDataSourceFactory $arrayDataSourceFactory,
) {
}

$domainData = [];
foreach ($this->domain->getAll() as $domainConfig) {
    $domainData[] = [
        'id' => $domainConfig->getId(),
        'name' => $domainConfig->getName(),
        'locale' => $domainConfig->getLocale(),
        'icon' => null,
    ];
}

$dataSource = $this->arrayDataSourceFactory->create($domainData, 'id');
```

## [`MoneyConvertingDataSourceDecorator`]({{github.link}}/packages/framework/src/Component/Grid/MoneyConvertingDataSourceDecorator.php)

A decorator that can be applied to any of the data sources described above. It converts monetary values in a data set to [`Money` value object](../../model/how-to-work-with-money.md#money-class).

### Example of usage

```php
public function __construct(
    private readonly \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    private readonly \Shopsys\FrameworkBundle\Component\Grid\MoneyConvertingDataSourceDecoratorFactory $moneyConvertingDataSourceDecoratorFactory,
) {
}

$innerDataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'u.id');

$dataSource = $this->moneyConvertingDataSourceDecoratorFactory->create($innerDataSource, ['ordersSumPrice']);
```
