# Adding Search to List Page

The CRUD Controller can display a quick search box above the list datagrid.
The administrator types a text, and the list is filtered to the records matching it.
The search is a case- and accent-insensitive substring match, and `*` / `?` can be used as wildcards for multiple / single characters.

The search term is passed as a GET parameter, so it is automatically preserved when the administrator pages or sorts the list.

## Enable quick search

Implement the `configureSearch()` method and declare which fields are searched:

```php
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;

public function configureSearch(SearchConfig $search): void
{
    $search->enableQuickSearch(
        fields: ['name', 'catnum'],
        placeholder: t('Search by name or catalog number…'),
    );
}
```

- `fields` accepts field paths in dot notation, the same way as datagrid columns: a field of the entity (`catnum`), a field of a many-to-one association (`orderStatus.name` joins the association), or a translated field (`name` resolves to the current-locale translation automatically, `translations.name` works too).
- `placeholder` is shown in the empty search input.
- The optional third parameter `infoMessage` displays an info icon with a tooltip next to the input — use it to tell the administrator what is searched.

All declared fields are combined with OR — a record matches when the text is found in any of them.

## Custom search logic

When the generated condition is not enough (searching a computed expression, exact matching, custom joins), replace it with a callback:

```php
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Symfony\Component\Uid\Uuid;

public function configureSearch(SearchConfig $search): void
{
    $search->enableQuickSearch(placeholder: t('Search by name or UUID…'))
        ->queryCallback(static function (QueryBuilder $queryBuilder, string $searchText): void {
            if (Uuid::isValid($searchText)) {
                $queryBuilder->andWhere('o.uuid = :uuid')->setParameter('uuid', $searchText);

                return;
            }

            $queryBuilder
                ->andWhere('NORMALIZED(o.name) LIKE NORMALIZED(:searchText)')
                ->setParameter('searchText', '%' . DatabaseSearchingHelper::getLikeSearchString($searchText) . '%');
        });
}
```

The callback receives the list query builder (root alias is always `o`) and the searched text, and fully replaces the condition generated from `fields`.

!!! note

    Quick search works only for lists backed by the ORM adapter (the default). Lists backed by an array datasource cannot be searched this way.

Extensions can configure search the same way — `configureSearch()` is available in `AbstractCrudControllerExtension` and receives the same `SearchConfig` instance, so a project can enable or reconfigure the search of an existing CRUD Controller.

## Advanced search

Advanced search lets the administrator combine multiple search rules, each consisting of a subject, an operator, and a value.
Registering at least one filter in `configureSearch()` displays the Quick search and Advanced search tabs above the list:

```php
use Shopsys\AdministrationBundle\Component\Search\Filter;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;

public function configureSearch(SearchConfig $search): void
{
    $search->addFilter(
        Filter::create('name', t('Name'))
            ->withOperators(Operator::CONTAINS, Operator::NOT_CONTAINS)
            ->apply(static function (QueryBuilder $queryBuilder, FilterRuleCollection $rules): void {
                foreach ($rules as $rule) {
                    $dqlOperator = $rule->operator === Operator::CONTAINS ? 'LIKE' : 'NOT LIKE';
                    $queryBuilder
                        ->andWhere(sprintf('NORMALIZED(o.name) %s NORMALIZED(:%s)', $dqlOperator, $rule->param()))
                        ->setParameter($rule->param(), $rule->getLikeValue());
                }
            }),
    );
}
```

- `Filter::create()` defines an inline filter: the internal name (used in the URL), the label shown in the subject select, the operators offered to the administrator (`Operator` enum), and the value widget (`withFormType()`, a text input by default).
- The `apply()` callback receives the list query builder (root alias `o`) and **all rules of this subject at once** as a `FilterRuleCollection`, so the filter decides how multiple rules combine (e.g. OR them into one `IN` condition).
- `$rule->param()` returns a query parameter name unique to the rule — never invent parameter names yourself, two rules of the same filter would collide.
- `$rule->getLikeValue()` converts the value to a `LIKE` pattern with `*` / `?` wildcard support.
- Rules with no operator or an empty value are skipped, and the search never fails on an invalid rule — a filter reports problems with `$rules->addRuleError($rule, ...)`, which shows as a form error on the rule row.
- For a filter reused across controllers, implement `Shopsys\AdministrationBundle\Component\Search\FilterInterface` in a dedicated class, inject it into the controller constructor, and register it with `addFilter()`.

When both searches are submitted at once, the advanced search wins. The rules are GET parameters, so an advanced search survives paging and sorting, and the Reset filter button clears it.
The filter preselected in a new rule is the first registered one; override it with `$search->setDefaultFilter('name')`.
