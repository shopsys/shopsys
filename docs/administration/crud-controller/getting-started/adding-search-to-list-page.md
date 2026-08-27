# Adding Search to List Page

The CRUD Controller can display a quick search box above the list datagrid.
The administrator types a text, and the list is filtered to the records matching it.
The search is a case- and accent-insensitive substring match, and `*` / `?` can be used as wildcards for multiple / single characters.

The search term is passed as a GET parameter, so it is automatically preserved when the administrator pages or sorts the list.

## Enable quick search

Implement the `configureSearch()` method and declare which fields are searched:

```php
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;

protected function configureSearch(SearchConfig $search): void
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
use Symfony\Component\Uid\Uuid;

protected function configureSearch(SearchConfig $search): void
{
    $search->enableQuickSearch(placeholder: t('Search by name or UUID…'))
        ->queryCallback(static function (QueryBuilder $queryBuilder, string $searchText): void {
            if (Uuid::isValid($searchText)) {
                $queryBuilder->andWhere('o.uuid = :uuid')->setParameter('uuid', $searchText);

                return;
            }

            $queryBuilder
                ->andWhere('NORMALIZED(o.name) LIKE NORMALIZED(:searchText)')
                ->setParameter('searchText', DatabaseSearchingHelper::getLikeSearchString($searchText));
        });
}
```

The callback receives the list query builder (root alias is always `o`) and the searched text, and fully replaces the condition generated from `fields`.

!!! note

    Quick search works only for lists backed by the ORM adapter (the default). Lists backed by an array datasource cannot be searched this way.

Extensions can configure search the same way — `configureSearch()` is available in `AbstractCrudControllerExtension` and receives the same `SearchConfig` instance, so a project can enable or reconfigure the search of an existing CRUD Controller.
