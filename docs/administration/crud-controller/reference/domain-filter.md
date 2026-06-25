# Domain filter

On the list page the CRUD Controller automatically recognizes whether the entity is domain-aware and, when it is, it:

1. renders a domain switcher/filter above the grid, and
2. scopes the grid to the selected domain (see the per-type behavior below).

No configuration is required for the common cases — the behavior is derived from the entity's Doctrine mapping.

## Automatic detection

The strategy (`DomainFilterType`) is detected from the entity's `ClassMetadata`:

| Type | Detected when | Query behavior (root alias `o`) |
| --- | --- | --- |
| `SCALAR` | the entity has a scalar `domainId` field (e.g. `Order`, `Inquiry`) | rows are filtered: `o.domainId = :selectedDomainId` |
| `COLLECTION` | the entity has a `OneToMany` association named `domains` whose target entity has a scalar `domainId` field (e.g. `Category` → `CategoryDomain`) | rows are **not** filtered; the selected domain's row is `LEFT JOIN`ed (see below) |
| `NONE` | neither of the above | no filter, the grid is rendered as-is |

### Why `COLLECTION` does not filter rows

For `SCALAR` entities each record belongs to exactly one domain, so `o.domainId = X` is a meaningful row filter.

`COLLECTION` entities are different: a record has one row in the `domains` collection for **every** domain (e.g. every `Category` has a `CategoryDomain` for each domain). Filtering by the mere existence of such a row would therefore match everything and do nothing. So for `COLLECTION` the resolver does **not** remove rows. Instead it `LEFT JOIN`s the row of the selected domain (a 1:1 join — rows are neither dropped nor multiplied) and exposes it under `CrudDomainFilterResolver::DOMAIN_JOIN_ALIAS`. The controller can then read that domain's per-domain columns (e.g. visibility) and, if it wants to actually filter rows, add its own condition in `configureQuery()`.

```php
use Shopsys\AdministrationBundle\Component\Crud\Domain\CrudDomainFilterResolver;

protected function configureQuery(QueryBuilder $queryBuilder): void
{
    if ($this->selectedDomainFilterId !== null) {
        // read the selected domain's row joined under the shared alias
        $queryBuilder->addSelect(CrudDomainFilterResolver::DOMAIN_JOIN_ALIAS . '.visible AS domainVisible');

        // optional: actually filter rows by a per-domain flag
        // $queryBuilder->andWhere(CrudDomainFilterResolver::DOMAIN_JOIN_ALIAS . '.visible = true');
    }
}
```

## Facade modes

Detection of the entity type is independent of which facade backs the switcher (`DomainFilterMode`):

- **`SWITCH`** (default) — uses `AdminDomainTabsFacade`, i.e. the globally selected domain shared across the administration (the same switch used in the top navigation). There is always exactly one selected domain; there is no "all domains" option.
- **`FILTER`** — uses `AdminDomainFilterTabsFacade` with a per-page namespace (the controller name). It adds an "all domains" option; when "all domains" is selected, the grid is not filtered.

## Overriding the behavior

All overrides are configured in the controller's `configure()` method.

```php
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\DomainFilterMode;
use Shopsys\AdministrationBundle\Component\Config\DomainFilterType;

public function configure(CrudConfig $config): void
{
    // Turn the filter off even though the entity is domain-aware
    $config->disableDomainFilter();

    // Force a strategy and/or use non-standard field/association names
    $config->configureDomainFilter(DomainFilterType::COLLECTION, association: 'domains');

    // Switch to the per-page filter with an "all domains" option
    $config->setDomainFilterMode(DomainFilterMode::FILTER);
}
```

## Custom query for non-standard entities

For entities that do not match the `SCALAR`/`COLLECTION` shapes, disable the automatic filter and build the query yourself in `configureQuery()`. The resolved selected domain id is available as `$this->selectedDomainFilterId` (it is `null` when no filter applies or "all domains" is selected in `FILTER` mode).

```php
protected function configureQuery(QueryBuilder $queryBuilder): void
{
    if ($this->selectedDomainFilterId !== null) {
        $queryBuilder
            ->andWhere('o.someCustomDomainRelation = :domainId')
            ->setParameter('domainId', $this->selectedDomainFilterId);
    }
}
```
