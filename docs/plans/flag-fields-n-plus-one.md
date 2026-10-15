# Flag fields N+1 in the Frontend API Implementation Plan

## Overview

Resolve the N+1 problem for all `Flag` fields in the Frontend API (Jira [SSP-1347](https://shopsys.atlassian.net/browse/SSP-1347)), so a GraphQL response listing N flags runs a constant number of SQL and Elasticsearch queries per field regardless of N. Fields affected: `name`, `categories` (named in the ticket) plus `seo` and `hreflangLinks` (AC "N+1 problem is solved for all flag fields").

The work follows the approach of the sibling tickets in the sprint – SSP-1346 / [#4950](https://github.com/shopsys/shopsys/pull/4950) (BlogCategory fields) and SSP-1355 / [#4934](https://github.com/shopsys/shopsys/pull/4934) (breadcrumbs): batch loaders in `packages/frontend-api`, Elasticsearch requests merged into one `_msearch`.

## Current State Analysis

| Field | Current resolution | Queries for N flags |
|---|---|---|
| `name` | `FlagResolverMap` → `$flag->getName($locale)` | `flags` query: 1 + N (translations not hydrated); other sources: 0 extra |
| `seo` | `FlagResolverMap` → `$flag->getSeoAttributes($domainId)` → lazy `Flag::$domains` (`EXTRA_LAZY`) | N |
| `hreflangLinks` | `HreflangLinksFacade::getForFlag()` → `createHreflangLink()` → `FriendlyUrlFacade` per domain | N × relevant domains |
| `categories` | `CategoryQuery::categoriesFilteredByProductFilterForFlagQuery()` → `CategoryFacade::getCategoriesOfProductByFilterData()` | N ES aggregations + N SQL |
| `slug` | `flagSlugBatchLoader` | already batched |
| `products` | `productsByEntitiesBatchLoader` | already batched |
| `products.productFilterOptions` | – | solved by SSP-1356 / #4938 |
| `breadcrumb` | `breadcrumbQuery` | flag detail only, out of scope |

Sources of `Flag` entities in the Frontend API:
- `flags` query → `FlagRepository::getAllVisibleFlags()` – joins `f.translations` but selects `f` twice (`->addSelect('f')`), so translations are **not** hydrated → bug causing the `name` N+1.
- `Product.flags` → `project-base` `App\FrontendApi\Model\Flag\FlagsBatchLoader` → joins and selects translations, OK.
- `FlagFilterOption.flag` → `ProductFilterConfigFactory` → `FlagRepository::getVisibleFlagsByIds()` – selects translations, OK.
- `flag(uuid|urlSlug)` → `getVisibleByUuid()` / `getVisibleFlagById()` – single entity, selects translations, OK.

## Desired End State

- All four fields are resolved with a constant number of queries for any number of flags in the response, from every source above (`flags`, `Product.flags`, `FlagFilterOption.flag`).
- GraphQL schema and response data unchanged.
- PR description contains a before/after table (SQL / ES counts from the Symfony profiler), same format as #4950.

### Key Discoveries:
- `packages/framework/src/Model/Product/Flag/FlagRepository.php:150` – `->addSelect('f')` must be `->addSelect('ft')`.
- `packages/framework/src/Model/Product/Flag/Flag.php:99` – `domains` is `EXTRA_LAZY`; `FlagDomain::$seo` is an embedded `SeoAttributes` (`FlagDomain.php:46`).
- `#4950` pattern for `seo`: `BlogCategoriesBatchLoader::loadSeoAttributesByBlogCategories()` + `BlogCategoryRepository::getBlogCategoryDomainsByBlogCategoriesAndDomainIdIndexedByBlogCategoryId()` – queries the `*Domain` entity directly, so the owning collection is never partially hydrated.
- `#4950` pattern for `hreflangLinks`: `BlogCategoryHreflangLinksBatchLoader` using `HreflangLinksFacade::getRelevantDomainIds()` (made public in #4950) and `FriendlyUrlSlugBatchLoader::getSlugsIndexedByEntityIdOnDomain()` (added in #4950). Flags have no per-domain visibility, so the flag variant drops the visibility step.
- `#4950` removed `HreflangLinksFacade::getForBlogCategory()` and builds the links in a Frontend API batch loader – for flags we keep the facade as the source of truth instead (Phase 3).
- The categories aggregation is produced by `FilterQuery::getAggregationQueryForProductCountInCategories()` (`packages/framework/src/Model/Product/Search/FilterQuery.php:1102`), which returns an **array** `{index, body}`, not a `FilterQuery`.
- SSP-1356 / #4938 (`TL/product-filter-options-n-1`) – needed by Phase 4:
  - replaces `MultipleSearchQuery(Factory)` with `Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchFacade::searchIndexedByKey(array<key, array{index, body}>)` – takes prebuilt query arrays, so the categories aggregation can be sent as is,
  - batches filter configs: `ProductFilterFacade::normalizeProductFilterDataByBatchLoadData(ProductFilterDataBatchLoadData[])` (+ `ProductFilterDataBatchLoadDataFactory::create($entity, $searchText, $productFilterData)`) and `ProductFilterFacade::getProductFilterData(Argument)` (mapping without normalization),
  - wires the batched normalization into `ProductsBatchLoader::loadByEntities()` (so `Flag.products(filter)` is batched), but `CategoryQuery::categoriesFilteredByProductFilterForFlagQuery()` still calls the normalization with **one item per flag** → N ES + N×SQL for filter configs remain.
- `ProductFilterDataMapper::mapFrontendApiFilterToProductFilterData()` loads parameters, parameter values, brands and flags by UUID (up to 4 SQL) on every call, i.e. once per flag even when all flags get the same `productFilter` argument.
- `ProductElasticsearchRepository::extractCategoryIdsAggregation()` (`packages/framework/src/Model/Product/Search/ProductElasticsearchRepository.php:202`) is protected.
- `CategoryFacade::getCategoriesOfProductByFilterData()` → `ProductOnCurrentDomainElasticFacade::getCategoryIdsForFilterData()` → `ProductElasticsearchRepository::getCategoryIdsForFilterData()` have no other caller than the flag `categories` field → they become dead code (phpstan dead-code check) and are removed.
- Dataloaders are registered in `project-base/app/config/packages/overblog_dataloader.yaml` (alphabetically) and injected via named args in `packages/frontend-api/src/Resources/config/services.yaml`.

## What We're NOT Doing

- `breadcrumb` – resolved only on flag detail.
- N+1 inside the returned `Category` objects (their own fields) – covered by category tickets (#4934 and follow-ups).
- Changing the admin usage of `FlagFacade::getAllVisibleFlags()` (`CategorySeoController:411`) – it only benefits from the select fix.
- Storefront changes.

## Implementation Approach

The branch `rv/flags-n-plus-one` is stacked on `TL/blog-category-n-1` (#4950, itself stacked on #4934), so it reuses the batch infrastructure from there (`AbstractElasticsearchBatchRepository`, `getSlugsIndexedByEntityIdOnDomain()`, public `getRelevantDomainIds()`). The PR targets `TL/blog-category-n-1` and is rebased onto `20.0` once #4934 and #4950 are merged.

Conflict with #4933 (`rv/product-elastic` → `20.0`, open): it deletes `project-base/app/src/FrontendApi/Model/Flag/{FlagRepository,FlagFacade,FlagsBatchLoader}.php` and moves the `Product.flags` loading to the framework – `FlagRepository::getByIds(array $flagIds, DomainConfig $domainConfig)` (visible flags with translation, indexed by ID, sorted by position) used by `Shopsys\FrontendApiBundle\Model\Flag\FlagsBatchLoader::loadByIds()`. Our Phase 2 commit adds the domains join to the project-base `getFlagsByIds()`. Wait until #4933 is in `20.0` (and therefore under the #4950 stack); when rebasing, resolve the conflict in the commit `flags in the Frontend API are loaded together with their domains` (see Phase 2 – "After #4933 is merged"). Do not add the domains join to #4933 itself (out of its scope).

Phase 4 needs #4938. On 2026-10-15 #4950 was not yet based on #4938 (on remote #4938 removes `MultipleSearchQuery` that #4950 builds on); Tomáš will restack #4950 onto #4938 and resolve it. Our branch keeps following `TL/blog-category-n-1` – **expect that branch to be rewritten** (force-pushes, restack): before starting any phase, `git fetch` and rebase our commits onto the current `origin/TL/blog-category-n-1` (base for `--onto` = the previous tip, never a fixed hash), and re-check the APIs listed in Key Discoveries (`MultipleSearchFacade`, `normalizeProductFilterDataByBatchLoadData()`, `getSlugsIndexedByEntityIdOnDomain()`, `getRelevantDomainIds()`). One PR for SSP-1347.

- `name`: fix the eager load.
- `seo`: eager load of **all** `f.domains` (no domain condition → the collection is complete, no signature change) in every query returning flags to the Frontend API. Decided 2026-10-15 instead of the originally planned `FlagsBatchLoader` over `FlagDomain` (#4950 pattern): flags have only five source queries, all under our control, so the batch loader was unnecessary overhead. Not added to the shared `getVisibleQueryBuilder()`, which is also used by `SitemapRepository` and luigis-box with their own selects.
- `hreflangLinks`: `HreflangLinksFacade::getForFlags()` (batch slugs per relevant domain via `FriendlyUrlFacade`), thin Frontend API batch loader on top.
- `categories`: batch loader – batched filter normalization (#4938 API) + one `_msearch` with the categories aggregation for all flags, then the existing `categoriesBatchLoader` (one SQL); mapping of the `productFilter` argument cached per request.

---

## Phase 0: Branch setup and baseline measurement

### Overview
Stack the branch and record the "before" numbers.

### Changes Required:

1. Rebase `rv/flags-n-plus-one` onto `origin/TL/blog-category-n-1` (branch currently has no own commits).
2. Measure with the Symfony profiler (SQL count + ES request count) on demo data, first domain:
   - `flags { name }`
   - `flags { seo { title description } }`
   - `flags { hreflangLinks { hreflang href } }`
   - `flags { categories { name } }`
   - `flags { categories(productFilter: { onlyInStock: true }) { name } }`
   - `products(first: 20) { flags { name seo { title } hreflangLinks { href } } }`
   - all fields together
3. Keep the numbers for the PR description (not committed).

### Success Criteria:

#### Automated Verification:
- [x] `git log --oneline origin/TL/blog-category-n-1..HEAD` is empty after rebase

#### Manual Verification:
- [x] Baseline table recorded

---

### Baseline (measured 2026-10-15, demo data, domain 1, 9 visible flags, warm caches unless noted)

| GraphQL query | SQL | ES |
|---|---|---|
| `__typename` (request overhead) | 1 | 0 |
| `flags { uuid }` | 2 | 0 |
| `flags { name }` | 11 | 0 |
| `flags { seo { title metaDescription h1 } }` | 11 | 0 |
| `flags { hreflangLinks }` – warm slug cache | 2 | 0 |
| `flags { hreflangLinks }` – cold slug cache (3 relevant domains) | 29 | 0 |
| `flags { slug }` – cold slug cache | 3 | 0 |
| `flags { categories { uuid } }` | 12 | 9 |
| `flags { categories(productFilter: { onlyInStock: true }) { uuid } }` | 31 | 18 |
| `products(first: 20) { flags { uuid } }` | 3 | 2 |
| `products(first: 20) { flags { name seo hreflangLinks } }` | 6 | 2 |
| `flags` – all fields | 30 | 9 |

`categories(productFilter: …)` doubles ES requests and adds ~19 SQL because `ProductFilterFacade::getProductFilterConfigForFlag()` builds the filter config per flag and `ProductFilterDataMapper` maps the same argument per flag – solved in Phase 4.

Measured by `measure.sh` + `count.php` (curl → `X-Debug-Token` → `FileProfilerStorage` in php-fpm, `db` + `shopsys.elasticsearch_collector` collectors), cold slug cache via `php project-base/app/bin/console cache:pool:clear main_friendly_url_slug_cache`.

## Phase 1: `name` – eager load translations

### Overview
Fix the select in `getAllVisibleFlags()`.

### Changes Required:

**File**: `packages/framework/src/Model/Product/Flag/FlagRepository.php`
**Changes**: `->addSelect('f')` → `->addSelect('ft')` in `getAllVisibleFlags()`.

```php
$flagsQueryBuilder = $this->getVisibleQueryBuilder()
    ->addSelect('ft')
    ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
```

### Success Criteria:

#### Automated Verification:
- [x] `FlagsTest`, `FlagTest` pass

#### Manual Verification:
- [x] `flags { name }` runs 1 SQL for flags (profiler)
- [ ] Admin category SEO mix flag choice still shows names

---

## Phase 2: `seo` – eager load of flag domains

### Overview
Fetch-join all `FlagDomain` rows in every query that returns flags to the Frontend API, so `Flag::getSeoAttributes($domainId)` reads the already initialized `$domains` collection.

### Changes Required:

**File**: `packages/framework/src/Model/Product/Flag/FlagRepository.php` – `getVisibleFlagsByIds()`, `getVisibleFlagById()`, `getAllVisibleFlags()`, `getVisibleByUuid()` use the extracted builder:

```php
protected function getVisibleWithTranslationAndDomainsQueryBuilder(string $locale): QueryBuilder
{
    return $this->getVisibleQueryBuilder()
        ->addSelect('ft', 'fd')
        ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
        ->leftJoin('f.domains', 'fd')
        ->setParameter('locale', $locale);
}
```

**File**: `project-base/app/src/FrontendApi/Model/Flag/FlagRepository.php` – `getFlagsByIds()` (`Product.flags`): `->select('f, ft, fd')` + `->leftJoin('f.domains', 'fd')`.

`FlagResolverMap::seo` stays `$flag->getSeoAttributes($this->domain->getId())`.

#### After #4933 is merged (rebase conflict resolution)
In the commit `flags in the Frontend API are loaded together with their domains`:
- drop the change of `project-base/app/src/FrontendApi/Model/Flag/FlagRepository.php` (the file no longer exists),
- make the framework `FlagRepository::getByIds()` from #4933 use the extracted builder instead:

```php
public function getByIds(array $flagIds, DomainConfig $domainConfig): array
{
    return $this->getVisibleWithTranslationAndDomainsQueryBuilder($domainConfig->getLocale())
        ->andWhere('f.id IN (:flagIds)')
        ->indexBy('f', 'f.id')
        ->orderBy('f.position', SortDirection::Ascending)
        ->setParameter('flagIds', $flagIds)
        ->getQuery()->getResult();
}
```

- re-measure `products(first: 20) { flags { name seo hreflangLinks } }` (expected 3 SQL as now) and rerun `FlagsTest`, `FlagTest`.

`getByIds()` and `getVisibleFlagsByIds()` then differ only in `indexBy` – they are merged in Phase 6.

### Success Criteria:

#### Automated Verification:
- [x] `FlagTest` (seo fields), `FlagsTest` (all flags at once) pass

#### Manual Verification:
- [x] `flags { seo { … } }` and `products { flags { seo { … } } }` run no extra SQL (measured: `flags { seo }` 11 → 2, `products(20) { flags { name seo hreflangLinks } }` 6 → 3)

---

## Phase 3: `hreflangLinks` – batch method in `HreflangLinksFacade`

### Overview
Decided 2026-10-15: unlike #4950 (where `BlogCategory` dropped out of `HreflangLinksFacade` and the hreflang building moved into a Frontend API batch loader), the framework facade stays the single source of truth for flags – it keeps `getForFlag()` and gets a batch variant; the Frontend API loader only collects the flags for it. This also reuses the framework's absolute URL building (incl. the homepage slug special case) instead of a copy in the loader.

### Changes Required:

#### 1. Separate commit before the hreflang commit – batch slugs in the framework
**File**: `packages/framework/src/Component/Router/FriendlyUrl/FriendlyUrlFacade.php` – `getMainFriendlyUrlSlugsIndexedByEntityId(int $domainId, string $routeName, int[] $entityIds): array<int, string>` (cache + one query for the missing slugs; logic moved from #4950's `FriendlyUrlSlugBatchLoader::getSlugsIndexedByEntityIdOnDomain()`, which now delegates to it and only prefixes `/`). Its cache tests moved to `packages/framework/tests/Unit/Component/Router/FriendlyUrl/FriendlyUrlFacadeTest.php`. Tomáš should know about it when restacking #4950.

#### 2. Hreflang commit
- `FriendlyUrlFacade::getAbsoluteUrlByMainFriendlyUrlSlug(int $domainId, string $mainFriendlyUrlSlug)` extracted from `getAbsoluteUrlByRouteNameAndEntityId()`.
- `HreflangLinksFacade::getForFlags(Flag[] $flags, int $currentDomainId, bool $absoluteUrl = true): HreflangLink[][]` – one slug query per relevant domain, links in the order of the flags. The unused `getForFlag()` is removed together with `Flag` from the `doGetHrefLinks()` union type (upgrade note). Unit test `packages/framework/tests/Unit/Model/Seo/HreflangLinksFacadeTest.php`.
- `packages/frontend-api/src/Model/Flag/FlagHreflangLinksBatchLoader.php` – `loadByFlags()` = `promiseAdapter->all($hreflangLinksFacade->getForFlags($flags, $domain->getId()))`.
- `FlagDetailFriendlyUrlDataProvider::ROUTE_NAME` protected → public, used in `FriendlyUrlSlugBatchLoader::loadFlagSlugs()`.
- Wiring: `overblog_dataloader.yaml` (`flagHreflangLinksBatchLoader`), `services.yaml` (`$flagHreflangLinksBatchLoader` for `FlagResolverMap`).

`BlogCategory` could get the same `getForBlogCategories()` – suggestion for Tomáš's #4950 review, not part of this PR.

### Success Criteria:

#### Automated Verification:
- [x] `HreflangLinksTest` (Flag data set, incl. multidomain) passes unchanged
- [x] unit tests `FriendlyUrlFacadeTest`, `HreflangLinksFacadeTest`, `FriendlyUrlSlugBatchLoaderTest`
- [ ] `php phing phpstan` passes (incl. dead-code check)

#### Manual Verification:
- [x] `flags { hreflangLinks { … } }` runs 1 friendly URL SQL per relevant domain (cold cache), 0 with warm cache

---

## Phase 4: `categories` – batched filter normalization, category IDs via `_msearch`, existing `categoriesBatchLoader`

### Overview
**Prerequisite: `TL/blog-category-n-1` restacked onto #4938** (`git merge-base --is-ancestor origin/TL/product-filter-options-n-1 origin/TL/blog-category-n-1`) and our branch rebased onto it. If the restack is not done yet, implement Phases 1–3 first.

A new batch loader collects all flags of the response, normalizes their filter data in one batch (#4938 API), sends one `_msearch` with the categories aggregation and returns only the **category IDs** per flag. The categories themselves are loaded by the existing `categoriesBatchLoader` (`packages/frontend-api/src/Model/Category/CategoriesBatchLoader.php`), which takes `int[]` keys, preserves order, filters by visibility on the current domain and joins translations – the dataloader merges the keys of all flags into one SQL. The mapping of the `productFilter` argument is cached per request, so the same argument is not mapped (SQL by UUIDs) once per flag.

Behaviour change: categories not visible on the current domain are no longer returned (the old `CategoryRepository::getCategoriesByIds()` did not check visibility). Mention it in the PR description.

### Changes Required:

#### 1. Cache of the filter argument mapping
**File**: `packages/frontend-api/src/Model/Product/Filter/ProductFilterDataMapper.php`
**Changes**: cache the loaded entities (parameters, parameter values, brands, flags) per request in `InMemoryCache`, keyed by the sorted list of UUIDs; `mapFrontendApiFilterToProductFilterData()` keeps creating a **new** `ProductFilterData` (and new `ParameterFilterData`) on every call. Entities are shared, the data objects are not – `removeExcessiveFilters()` and `addListingFlagToProductFilterData()` mutate `ProductFilterData`, so caching the whole result would leak normalization of one flag into another.

This benefits every listing mapping the same argument several times in one request (flags, brands, categories), not only flags.

#### 2. Batch repository method for category IDs
**File**: `packages/frontend-api/src/Model/Product/BatchLoad/ProductElasticsearchBatchRepository.php` (in the shape it has after #4938)
**Changes**:

```php
/**
 * @param array<int|string, \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery> $filterQueriesIndexedByKey
 * @return array<int|string, int[]> category ids indexed by the same keys
 */
public function getBatchedCategoryIdsByFilterQueries(array $filterQueriesIndexedByKey): array
{
    $responsesIndexedByKey = $this->multipleSearchFacade->searchIndexedByKey(array_map(
        static fn (FilterQuery $filterQuery): array => $filterQuery->setLimit(0)->getAggregationQueryForProductCountInCategories(),
        $filterQueriesIndexedByKey,
    ));

    return array_map(
        fn (array $response): array => $this->productElasticsearchRepository->extractCategoryIdsAggregation($response),
        $responsesIndexedByKey,
    );
}
```

**File**: `packages/framework/src/Model/Product/Search/ProductElasticsearchRepository.php` – `extractCategoryIdsAggregation()` protected → public (if still present after #4938, otherwise move the extraction here).

#### 3. Batch load data + loader of category IDs
**Files** (new, `packages/frontend-api/src/Model/Flag/`):
- `FlagCategoryIdsBatchLoadData` (`Flag $flag`, `ProductFilterData $productFilterData`) + `FlagCategoryIdsBatchLoadDataFactory` – pattern `BlogArticlesByBlogCategoryBatchLoadData(Factory)` from #4950.
- `FlagCategoryIdsBatchLoader::loadByBatchLoadData(FlagCategoryIdsBatchLoadData[]): Promise` – follows `ProductsBatchLoader::loadByEntities()` from #4938:
  1. `ProductFilterFacade::normalizeProductFilterDataByBatchLoadData()` with `ProductFilterDataBatchLoadDataFactory::create($flag, '', $productFilterData)` for all items (one filter config batch for all flags),
  2. set the listing flag: `$productFilterData->flags = [$flag]` (keeps the current semantics of the field – categories of products with this flag, other flag filters are replaced),
  3. `FilterQueryFactory::createListableWithProductFilter($productFilterData)` per item,
  4. `getBatchedCategoryIdsByFilterQueries()`,
  5. `promiseAdapter->all()` of the `int[]` lists in input order.
- Wiring: `overblog_dataloader.yaml` – `flagCategoryIdsBatchLoader` (alias `flag_category_ids_batch_loader`).

#### 4. Resolver
**File**: `packages/frontend-api/src/Model/Resolver/Category/CategoryQuery.php`
**Changes**: `categoriesFilteredByProductFilterForFlagQuery()` only maps the argument (`ProductFilterFacade::getProductFilterData()`), the normalization moves into the batch loader; return type `array` → `Promise`:

```php
$argument['filter'] = $argument['productFilter'];
$productFilterData = $this->productFilterFacade->getProductFilterData($argument);

return $this->flagCategoryIdsBatchLoader
    ->load($this->flagCategoryIdsBatchLoadDataFactory->create($flag, $productFilterData))
    ->then(fn (array $categoryIds) => $this->categoriesBatchLoader->load($categoryIds));
```

New constructor dependencies `$flagCategoryIdsBatchLoader` (`@flag_category_ids_batch_loader`), `$categoriesBatchLoader` (`@categories_batch_loader`) and the data factory, wired in `packages/frontend-api/src/Resources/config/services.yaml`; `ProductFilterDataBatchLoadDataFactory` added by #4938 is no longer needed in `CategoryQuery`.

#### 5. Remove dead code
- `CategoryFacade::getCategoriesOfProductByFilterData()` (+ `@method` in `project-base/app/src/Model/Category/CategoryFacade.php`)
- `ProductOnCurrentDomainElasticFacade::getCategoryIdsForFilterData()`
- `ProductElasticsearchRepository::getCategoryIdsForFilterData()`
(verify against the post-#4938 code, #4938 changes both facades)

#### 6. Upgrade notes
`upgrade-notes/backend_*.md` – only BC impacts:
- removed methods from step 5, `HreflangLinksFacade::getForFlag()` (use `getForFlags()`) and `Flag` from the `doGetHrefLinks()` union type (+ Phase 6 changes),
- `FlagRepository::getVisibleFlagsByIds()` and `getVisibleFlagById()` no longer return hidden flags (separate fix commit),
- `CategoryQuery::categoriesFilteredByProductFilterForFlagQuery()` returns `Promise`,
- `Flag.categories` no longer returns categories hidden on the current domain.

### Success Criteria:

#### Automated Verification:
- [ ] Unit test `ProductElasticsearchBatchRepository::getBatchedCategoryIdsByFilterQueries()` (keys preserved)
- [ ] Unit test `FlagCategoryIdsBatchLoader` (order of IDs per flag, flag with no categories, normalization called once for all items)
- [ ] Unit test `ProductFilterDataMapper` – same argument mapped twice → entities loaded once, two distinct `ProductFilterData` instances
- [ ] Functional tests (Phase 5) pass

#### Manual Verification:
- [ ] `flags { categories { name } }` → 1 ES request + 1 SQL for categories (including translations)
- [ ] `flags { categories(productFilter: …) }` → filter configs in one batch, argument mapped once (compare with the baseline 31 SQL / 18 ES)
- [ ] Storefront flag detail page lists the same categories as before (with and without filter)

---

## Phase 5: Functional tests and measurement

### Changes Required:

**Directory**: `project-base/app/tests/FrontendApiBundle/Functional/Product/Flag/`
- `FlagsTest`: new test querying `flags { name seo { title description } hreflangLinks { hreflang href } categories { uuid } }` for **all** flags at once, expected data built from facades (verifies that batching maps results back to the right flag).
- `FlagTest`: `categories(productFilter: …)` on flag detail – filter still applied.
- New `.graphql` files in `graphql/`.

### Success Criteria:

#### Automated Verification:
- [ ] Frontend API functional tests: `docker compose exec php-fpm php phing tests-functional` (Flag, Hreflang, Slug tests)
- [ ] Unit tests: `docker compose exec php-fpm php phing tests-unit`
- [ ] Standards: `docker compose exec php-fpm php phing standards-fix` + phpstan
- [ ] GraphQL schema unchanged: `make generate-schema` produces no diff

#### Manual Verification:
- [ ] After-measurement table for all queries from Phase 0 for the PR description

---

## Phase 6: Cleanup – merge `getByIds()` into `getVisibleFlagsByIds()`

### Overview
**Prerequisite: #4933 and #4938 in the base of the branch.** A separate commit at the end of the branch. After #4933 `FlagRepository::getByIds(array $flagIds, DomainConfig $domainConfig)` and `getVisibleFlagsByIds(array $flagsIds, string $locale)` load the same thing (visible flags with translation and domains, sorted by position), they differ only in the result being indexed by ID – and both remaining callers want it indexed by ID:

| Caller (after #4933 + #4938) | Method | Uses |
|---|---|---|
| `Shopsys\FrontendApiBundle\Model\Flag\FlagsBatchLoader::loadByIds()` (#4933) | `getByIds()` | indexed by ID |
| `ProductFilterConfigFactory::getFlagsIndexedById()` (#4938) | `getVisibleFlagsByIds()` | wraps the result in its own `indexById()` |

(`project-base` `ProductFilterConfigFactory` calling `getVisibleFlagsByIds()` is deleted by #4938 – verify.)

Keep `getVisibleFlagsByIds()`: its name says the flags are visible (the original `getByIds()` returned all flags by `findBy()`, #4933 changed its meaning) and it takes only what it needs (`string $locale`, not `DomainConfig`).

### Changes Required:

**File**: `packages/framework/src/Model/Product/Flag/FlagRepository.php` + `FlagFacade.php`
- `getVisibleFlagsByIds()` returns `array<int, Flag>` indexed by ID, sorted by position (`->indexBy('f', 'f.id')`); docblock as in #4933's `getByIds()`,
- remove `getByIds()` (both classes).

**File**: `packages/frontend-api/src/Model/Flag/FlagsBatchLoader.php` – `loadByIds()` calls `getVisibleFlagsByIds(array_merge(...$flagsIds), $this->domain->getLocale())`.

**File**: `packages/framework/src/Model/Product/Filter/ProductFilterConfigFactory.php` – `getFlagsIndexedById()` returns the result directly without `indexById()`; `indexById()` itself stays (still used by `getBrandsIndexedById()`).

**Tests**: adjust unit tests of `ProductFilterConfigFactory` (#4938) / `FlagsBatchLoader` (#4933) that mock `getVisibleFlagsByIds()` / `getByIds()`.

**Upgrade notes** (same file as the rest of this PR):
- `FlagFacade::getByIds()` and `FlagRepository::getByIds()` were removed, use `getVisibleFlagsByIds()` (if #4933 is released together with this PR, check its upgrade note about the changed `getByIds()` signature – they must not contradict each other),
- `getVisibleFlagsByIds()` now returns the flags indexed by their ID.

### Success Criteria:

#### Automated Verification:
- [ ] `FlagsTest`, `FlagTest`, `ProductsFilteringOptionsTest`, `CategorySeoTest` pass
- [ ] unit tests of `ProductFilterConfigFactory` and `FlagsBatchLoader` pass
- [ ] phpstan incl. dead-code check (no leftover `getByIds()` callers)

#### Manual Verification:
- [ ] `products(first: 20) { flags { name } }` keeps flag order by position; filter options list flags in the same order as before

---

## Testing Strategy

### Unit Tests:
- `FlagHreflangLinksBatchLoader::loadByFlags()` – multiple relevant domains, order.
- `ProductElasticsearchBatchRepository::getBatchedCategoryIdsByFilterQueries()` – keys.
- `FlagCategoryIdsBatchLoader` – order of IDs per flag, empty result, one normalization batch.
- `ProductFilterDataMapper` – entity lookups cached, result objects not shared.

### Functional Tests:
- All flags with all batched fields in one query – correct data per flag.
- `categories` with `productFilter`.
- Existing `HreflangLinksTest`, `FlagTest`, `FlagsTest`, Slug tests unchanged.

### Manual Testing Steps:
1. Storefront flag detail – categories, SEO meta, hreflang `<link>` tags unchanged.
2. Product list – flag badges (names) unchanged.
3. Profiler comparison against the Phase 0 baseline.

## Performance Considerations

- `categories`: N ES requests → 1 `_msearch`; N SQL → 1; with `productFilter` also filter configs in one batch and the argument mapped once.
- `hreflangLinks`: uses the main friendly URL slug cache (`main_friendly_url_slug_cache`), so warm requests do no SQL.
- `seo`: no extra SQL, `flag_domains` rows are fetch-joined (one row per flag and domain).

## Migration Notes

No DB migration, no schema change. Upgrade notes only for removed methods and the changed return type.

## References

- Jira: [SSP-1347](https://shopsys.atlassian.net/browse/SSP-1347)
- Sibling work: SSP-1346 / #4950, SSP-1355 / #4934, SSP-1356 / #4938, SSP-4472 / #4937
- `BlogCategoriesBatchLoader`, `BlogCategoryHreflangLinksBatchLoader`, `BlogArticlesBatchLoader` on `TL/blog-category-n-1`
- `packages/frontend-api/src/Model/Flag/FlagResolverMap.php`
- `packages/frontend-api/src/Resources/config/graphql-types/ModelType/Product/Flag/FlagDecorator.types.yaml`
