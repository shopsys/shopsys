# Server-side stránkování produktů v produktových listech (SSP-4608)

## Overview

`ProductList.products` dnes vrací všechny produkty listu najednou (`[Product!]!`). Cílem je server-side stránkování: nahradit pole `products` polem `productsConnection` typu existující `ProductConnection` (relay connection se `first`/`after`/`last`/`before`).

Stavíme od nuly nad `21.0` na větvi `rv/product-list-connection`. PR [#5024](https://github.com/shopsys/shopsys/pull/5024) slouží jen jako předloha (zejména pro řešení viditelnosti, viz níže), ne jako základ. Zadání Jiry (zachovat původní `products`) neplatí: původní pole nemá reálného konzumenta, BC promise se neřeší, případný projekt si to doimplementuje sám.

## Current State Analysis

- `ProductListDecorator` (`packages/frontend-api/src/Resources/config/graphql-types/ModelType/ProductList/ProdutcListDecorator.types.yaml`) definuje `itemsCount` a `products`.
- `ProductsQuery::productsByProductListQuery()` (`packages/frontend-api/src/Model/Resolver/Products/ProductsQuery.php:89`) vezme **všechna** ID listu (`ProductListFacade::getProductIdsByProductList`) a pošle je do `productsVisibleAndSortedByIdsBatchLoader`. Nestránkuje se a načítají se plné ES dokumenty všech produktů.
- `productsCountByProductListQuery()` (`ProductsQuery.php:96`) pro `itemsCount` načítá všechny produkty jen kvůli `count()`.
- Pořadí v listu je **custom**: `ProductListRepository::getProductIdsByProductList()` řadí `pli.position ASC`, `pli.id DESC` (nově přidané nahoře). `ProductListFacade::moveProduct()` mění `position`.
- ID v DB zahrnují i produkty, které nejsou na aktuální doméně viditelné. `productsVisibleAndSortedByIdsBatchLoader` je tiše vynechá. Při stránkování nad DB ID by proto stránky vycházely kratší a `totalCount` by nesouhlasil.
- Existující `ProductConnection` (`packages/frontend-api/src/Model/Product/Connection/ProductConnection.php`) nese `totalCount`, `productFilterOptions` (lazy closure), `orderingMode`, `defaultOrderingMode`. GraphQL typ je v `project-base/app/config/graphql/types/ModelType/Product/ProductConnection.types.yaml`. Pole `productFilterOptions: ProductFilterOptions!` a `orderingMode: ProductOrderingModeEnum!` jsou non-null.
- `ProductConnectionFactory` má jen metody pro kategorii/značku/vlajku/vše. Pro list žádná není. Všechny closure filtrů počítají nad kategorií/značkou/vlajkou/katalogem, ne nad ID listu.
- Konzumenti ve storefrontu (`project-base/storefront`):
    - `graphql/requests/productLists/fragments/ProductListFragment.graphql` (`products { ...ProductInProductListFragment }`), používá ho `ProductListQuery` i mutace `AddProductToList`, `RemoveProductFromList`, `MoveProductInList`.
    - `utils/productLists/useProductList.ts`: `isProductInList()` dělá `currentProductList.products.find(...)`. Tohle je důvod, proč PR #5024 přidal `productUuids`: s jednou stránkou produktů už nelze poznat, zda je produkt v listu (srdíčko u výpisů).
    - `urql/cache/updates.ts`: `manuallyUpdateProductListQuery` počítá `itemsCount: result.products.length` a ručně přepisuje cache `ProductListQuery`/`ProductListCountQuery`.
    - Stránky porovnání (`utils/productLists/comparison/*`, `components/Pages/ProductComparison/*`) a `pages/wishlist.tsx`.
- Mimo storefront `products` na `ProductList` nikdo nečte (ověřeno grepem, `productsByProductListQuery` je jen v decorator YAML a `ProductsQuery`).

## Desired End State

- `ProductList.productsConnection(first, after, last, before): ProductConnection!` vrací jen viditelné produkty v pořadí listu, stránkované na serveru, se správným `totalCount`.
- `ProductList.products` a resolver `productsByProductListQuery` neexistují.
- `itemsCount` se počítá bez načítání plných produktů.
- Storefront čte listy přes `productsConnection` a funguje stav „je v listu“ bez načtení celého listu.
- Pole `productFilterOptions` na connection listu je lazy, bez dopadu na výkon, dokud se na něj klient nezeptá. Reálná implementace filtrů je mimo rozsah (viz níže).

### Key Discoveries

- `ProductConnection::getProductFilterOptions()` vyvolá closure až při resolvování pole, takže nepoužité pole nic nestojí (`ProductConnection.php`, `ProductConnectionFactory.php`).
- `ProductConnectionDecorator` v packages je jen prázdná značka (`type: object`, `decorator: true`). Pole definuje až project-base `ProductConnection`. Soubor jsme přejmenovali z `ProductDecoratorConnection.types.yaml` (commit `d1dba8c8cf`), protože se špatně hledal.
- `Paginator::auto()` z overblog nastavuje `totalCount` jen u plain `Connection`. Pro `ProductConnection` je nutné použít vlastní `ConnectionBuilder` (jako `ProductConnectionFactory::createPaginator()`).

## What We're NOT Doing

- Nezachováváme `ProductList.products` ani žádnou BC vrstvu.
- Nevytváříme samostatný connection typ pro listy (`ProductListProductConnection` z PR #5024).
- Neimplementujeme filtry nad produkty listu. Pole `productFilterOptions` jen zůstává dostupné přes sdílený typ.
- Nepřidáváme nová řazení listu. Řazení zůstává `position ASC, id DESC`.
- Nepřidáváme `productUuids`, dokud není rozhodnuto, jak řešit stav „je v listu“ (viz Rozhodnutí 2).

## Rozhodnutí, která je třeba udělat před implementací

### Rozhodnutí 1: `orderingMode` a `defaultOrderingMode` u listu

Pořadí produktů v listu je custom (`position`), ale `ProductOrderingModeEnum` zná jen `PRIORITY`, `PRICE_ASC`, `PRICE_DESC`, `NAME_ASC`, `NAME_DESC`, `RELEVANCE`. Pole `orderingMode` na `ProductConnection` je non-null `ProductOrderingModeEnum!`.

Varianty:

1. **Nová hodnota enumu** (např. `CUSTOM` / `LIST_POSITION`) v `ProductOrderingModeEnumDecorator` + konstanta v `ProductListOrderingConfig`. List ji vrací vždy. Pro: pravdivé. Proti: nová hodnota je vidět v enumu u všech výpisů, kde nedává smysl (kategorie, značka, vyhledávání nesmí tuto hodnotu přijmout jako argument).
2. **Zeslabit `orderingMode` na nullable** a u listu vracet `null`. Pro: nic nelže. Proti: změna schématu u všech výpisů (BC pro `ProductConnection`, storefront musí ošetřit `null`).
3. **Vracet `PRIORITY`.** Pro: žádná změna schématu. Proti: zavádějící, storefront by mohl nabídnout řazení, které list nepodporuje.

Rozhodnutí je otevřené. Dopad na plán: Fáze 2 (factory) a Fáze 4 (storefront) se liší jen v tom, co se předá jako `orderingMode`/`defaultOrderingMode`. Navíc je potřeba rozhodnout, zda se v budoucnu bude řazení listu přidávat. Pak by se řazení muselo promítnout už do `getProductIdsByProductList`, protože stránkování stojí na pevném pořadí ID.

### Rozhodnutí 2: jak storefront pozná, že je produkt v listu

`isProductInList()` dnes používá celý seznam `products`. Se stránkováním nelze.

Varianty:

1. **`ProductList.productUuids: [Uuid!]!`** (přístup PR #5024): jedno lehké pole s ID všech viditelných produktů listu. Pro: jednoduché, málo dat. Proti: neomezený seznam, potřebuje `complexity`, přenáší se i při velkých listech.
2. **Pole na `Product`** (např. `isInProductList(type)`) resolvované batchem: dobré pro výpisy, ale mění `Product` a cache produktů.
3. **Nechat to na klientu** (lokální stav + mutace vrací nové `itemsCount`/pozici): nejméně backendu, ale riziko rozjetí stavu.
4. **Dotaz „které z těchto produktů jsou v listu“** (varianta navržená uživatelem): vstupem je seznam UUID produktů (typicky ty, které jsou právě vidět na stránce výpisu), výstupem informace, které z nich jsou v listu. Čte se z DB (`ProductListItem`), ne z ES.
    - Tvar výstupu: buď `[Boolean!]!` zarovnané se vstupem, nebo podmnožina `[Uuid!]!` (odolnější, neváže se na pořadí). Preferuje se podmnožina UUID.
    - Umístění: pole na `ProductList` (např. `containsProducts(productUuids: [Uuid!]!): [Uuid!]!`) nebo samostatná query s `ProductListInput`. Pole na `ProductList` znovu využije existující resolvování listu (typ + uuid + zákazník).
    - Dotaz: `WHERE pli.productList = :list AND p.uuid IN (:uuids)`. Viditelnost se nekontroluje, protože vstup tvoří produkty, které už stránka zobrazila. Je nutné omezit velikost vstupu (max. velikost stránky, validace + `complexity`).
    - Proč ne export do ES: stav je per zákazník a per list, takže nepatří do sdíleného product dokumentu, a export je asynchronní, takže by po kliknutí na srdíčko vracel zastaralá data.
    - Pro: okamžité a konzistentní, ohraničené vstupem, nepřenáší celý list. Proti: další request na výpisech (jde batchovat s výpisem), storefront musí stav držet v cache a po mutaci ho lokálně aktualizovat.
    - Rozšíření: stejné pole by mohlo sloužit i detailu produktu.

Rozhodnutí je otevřené. Před ním je nutné ověřit, jak výpisy produktů (kategorie, vyhledávání) dnes srdíčko ukazují, protože `useProductList` je pro ně zdrojem pravdy.

## Implementation Approach

Ordered by commit (BE a storefront odděleně, mezi commity smí být storefront rozbitý):

1. `renamed ProductDecoratorConnection.types.yaml ...` (**hotovo**, `d1dba8c8cf`).
2. BE: viditelná ID listu + `productsConnection` + smazání `products`.
3. BE: testy.
4. SF: přechod na `productsConnection` + stav „v listu“.
5. Generované soubory (`schema.graphql`, `types.ts`, `schema.md`, `schema-compressed.graphql.json`).
6. Upgrade notes (jen BC dopad).
7. `docs:` commit s tímto plánem (na konci, ke zrušení před merge).

## Phase 1: Backend – viditelná, uspořádaná ID listu

### Overview

Stránkovat lze jen nad viditelnými produkty, jinak `totalCount` i velikost stránek nesedí. Navíc nechceme načítat plné dokumenty všech produktů listu.

### Changes Required

#### 1. `ProductListApiFacade`

**File**: `packages/frontend-api/src/Model/Product/ProductList/ProductListApiFacade.php`
**Changes**: nová metoda vracející `Promise` s viditelnými ID listu ve stored pořadí.

- Vzít `ProductListFacade::getProductIdsByProductList()` (pořadí `position`, `id DESC`).
- Přes `FilterQueryFactory::createVisible()->filterByProductIds($chunk)` s `restrictFields([ID])` a `setLimit(count($chunk))` poslat dotazy po `FilterQuery::DEFAULT_LIMIT` přes `elasticsearch_search_batch_loader`.
- Výsledek přeskládat podle původního pořadí ID.
- Předloha: `ProductListApiFacade::getVisibleProductIdentifiers()` z PR #5024. V 21.0 ověřit existenci `ProductElasticsearchRepository::extractProductIdentifiersFromFields()`, případně přidat.

**Services**: přidat argument `$elasticsearchSearchBatchLoader` v `packages/frontend-api/src/Resources/config/services.yaml`.

### Success Criteria

#### Automated Verification

- [ ] Unit test facade: pořadí zachováno, neviditelné produkty vynechány, více chunků
- [ ] `docker compose exec php-fpm php phing standards-fix`

---

## Phase 2: Backend – `productsConnection` na `ProductList`

### Changes Required

#### 1. `ProductConnectionFactory`

**File**: `packages/frontend-api/src/Model/Product/Connection/ProductConnectionFactory.php`
**Changes**: nová metoda např. `createConnectionPromiseForProductList(...)`. Paginator v `MODE_PROMISE` s `ConnectionBuilder`, který vyrobí `ProductConnection` (jako `createPaginator()`), `totalCount = count(viditelná ID)`.

- `productFilterOptionsClosure` pro list: dokud nejsou filtry v rozsahu, closure vyhodí srozumitelnou výjimku (nebo vrací prázdné options, rozhodnout při implementaci a popsat v docblocku). Pole se nepoužívá, takže nic nestojí.
- `orderingMode`, `defaultOrderingMode`: podle Rozhodnutí 1.

#### 2. `ProductsQuery`

**File**: `packages/frontend-api/src/Model/Resolver/Products/ProductsQuery.php`
**Changes**:
- Nová `productsConnectionByProductListQuery(ProductList $productList, Argument $argument): Promise`: `pageSizeValidator->checkMaxPageSize()`, `setDefaultFirstOffsetIfNecessary()`, viditelná ID z Fáze 1, stránkování přes `array_slice($ids, $offset, $limit)` do `productsVisibleAndSortedByIdsBatchLoader`.
- Smazat `productsByProductListQuery()`.
- `productsCountByProductListQuery()` přepsat na počet viditelných ID z Fáze 1 (bez načítání produktů).
- Přidat závislost `ProductListApiFacade` do konstruktoru (`ProductListFacade` zůstává, dokud ho něco používá, případně odebrat).

#### 3. GraphQL schéma

**File**: `packages/frontend-api/src/Resources/config/graphql-types/ModelType/ProductList/ProdutcListDecorator.types.yaml`
**Changes**: odebrat `products`, přidat

```yaml
productsConnection:
    type: "ProductConnection!"
    description: "Paginated products of the list that are visible on the current domain, in the order of the list"
    argsBuilder: "Relay::Connection"
    resolve: '@=query("productsConnectionByProductListQuery", value, args)'
```

`ProductConnection` je definovaný v project-base (včetně `totalCount`, `productFilterOptions`, `orderingMode`), takže nic nového se do project-base nepřidává.

### Success Criteria

#### Automated Verification

- [ ] GraphQL test: stránkování `first`/`after`, `totalCount`, prázdný list, list s neviditelným produktem, pořadí po `MoveProductInList`
- [ ] `docker compose exec php-fpm php phing standards-fix`
- [ ] `make generate-schema`

#### Manual Verification

- [ ] Dotaz bez `productFilterOptions` nespouští ES dotaz na filtry (profiler/log)

---

## Phase 3: Backend – testy

- Rozšířit existující GraphQL testy product listů (`backend-tests` skill).
- Unit test `ProductListApiFacade` (pořadí, chunky, viditelnost).
- Případně unit test `ProductConnectionFactory::createConnectionPromiseForProductList`.
- Po změně konstruktorů smazat `var/cache/test` (phpunit běží s `APP_DEBUG=false`).

---

## Phase 4: Storefront

Samostatné commity, oddělené od BE.

### Changes Required

- `ProductListFragment.graphql`: místo `products` použít `productsConnection(first: …) { totalCount edges { node { ...ProductInProductListFragment } } }`.
- Stav „je produkt v listu“ podle Rozhodnutí 2 (`useProductList.ts: isProductInList`).
- `urql/cache/updates.ts`: `manuallyUpdateProductListQuery` nesmí počítat `itemsCount` z `result.products.length` (použít `totalCount`).
- Stránky porovnání a wishlist: přechod na connection (načítání po stránkách / „načíst další“ podle UX rozhodnutí).
- Vitest testy k `useProductList`, `useComparisonProducts`, `getComparisonParameters`, `useComparisonPage`.
- Po změně schématu: `types.ts`, `*.generated.tsx`, `schema.md`, `schema-compressed.graphql.json` (skill `storefront-tests`, `shopsys-commands`).
- Po úpravě překladů/JSON vyprázdnit Redis klíče `*fe:translates*`, pokud se přidají nové texty.

### Success Criteria

#### Automated Verification

- [ ] `docker compose exec storefront pnpm run typecheck`
- [ ] `docker compose exec storefront pnpm run test` (Vitest)
- [ ] Biome/knip bez chyb

#### Manual Verification

- [ ] Wishlist i porovnání fungují po přidání, odebrání i přesunu produktu
- [ ] Srdíčka u výpisů se správně přepínají
- [ ] Počet položek v hlavičce odpovídá `totalCount`

---

## Phase 5: Upgrade notes

Jen BC dopad: odebrané pole `ProductList.products` (nahrazeno `productsConnection`). Bez konstruktorů a aditivních změn. Samostatně pro backend a storefront (`upgrade-notes/`, skill `generate-upgrade-notes`).

## Testing Strategy

- PHPUnit GraphQL testy: stránkování, `totalCount`, viditelnost, pořadí, prázdný list.
- Unit testy facade a factory.
- Vitest pro storefront hooks.
- Ruční ověření wishlistu a porovnání s více než jednou stránkou produktů (demo data, nastavit malý `first`).

## Performance Considerations

- Načítají se jen ID (restrict fields) ve chunkách po `FilterQuery::DEFAULT_LIMIT`, plné dokumenty jen pro aktuální stránku.
- `itemsCount` už nenačítá plné produkty.
- `productFilterOptions` je lazy a nepoužité pole nic nestojí.
- Ověřit zátěž u velkého listu (stovky položek): počet ES dotazů = `ceil(n / DEFAULT_LIMIT)` + 1.

## Migration Notes

Žádná migrace dat. Změna je jen v GraphQL API. Starší projekty, které `ProductList.products` čtou, musí přejít na `productsConnection`.

## References

- Jira: SSP-4608
- PR (předloha): https://github.com/shopsys/shopsys/pull/5024
- `packages/frontend-api/src/Model/Resolver/Products/ProductsQuery.php`
- `packages/frontend-api/src/Model/Product/Connection/ProductConnectionFactory.php`
- `packages/framework/src/Model/Product/List/ProductListRepository.php` (řazení listu)
- `project-base/storefront/utils/productLists/useProductList.ts`
