# FE API: produktová data vždy z Elasticsearch – implementační plán

## Overview

Typ `Product` (a `RegularProduct` / `Variant` / `MainVariant`) ve Frontend API se dnes resolvuje dvojím způsobem: z ES pole (`ProductArrayFieldMapper`) nebo z Doctrine entity (`ProductEntityFieldMapper`), podle toho, co resolver rodičovského typu vrátí. Cílem je, aby se **vždy** použilo ES pole. Tam, kde dnes do typu `Product` přichází entita (objednávka, košík, reklamace, …), se produkt automaticky dávkově doptá do ES podle ID existujícím DataLoaderem `products_visible_by_ids_batch_loader` – centrálně, jedním listenerem, který obalí resolvery všech polí typu `Product`, bez úprav jednotlivých resolverů. `ProductEntityFieldMapper` a veškeré větvení `instanceof Product` zmizí.

Jira: [SSP-4418](https://shopsys.atlassian.net/browse/SSP-4418)

## Current State Analysis

- `ProductResolverMap::mapProduct()` vybírá mapper podle `$value instanceof Product` – `packages/frontend-api/src/Model/Resolver/Products/ProductResolverMap.php:59`, totéž v App override `project-base/app/src/FrontendApi/Resolver/Products/ProductResolverMap.php:32`. `RESOLVE_TYPE` větví stejně (`:32-33`).
- Oba mappery existují v package i v project-base (`App\FrontendApi\Resolver\Products\DataMapper\…`) a liší se sémantikou (sklad, link, příslušenství, cena, viditelnost, breadcrumb, data živě z DB vs. exportovaná).
- **Entitu do typu `Product` dnes posílají:**
  1. `OrderItem.product` (nullable) – `packages/frontend-api/src/Model/Resolver/Order/OrderItemResolverMap.php` (closure `product`, vrací `getProduct()` / `getProductGift()`)
  2. `CartItem.product: Product!` – bez resolveru, default getter `CartItem::getProduct()`; přes `Cart.items`, `AddProductResult.cartItem` a 5 seznamů v `CartItemModificationsResult`
  3. `ComplaintItem.product` (nullable) – default getter `ComplaintItem::getProduct()`
  4. `CartMultipleAddedProductModificationsResult.notAddedProducts: [Product!]!` – `packages/frontend-api/src/Model/Mutation/Cart/CartMutation.php:123-138` → `CartWithModificationsResult::addProductsNotAddedByMultipleAddition()` (`:445`)
  5. `ProductsByTransportUnavailabilityReason.products: [Product!]!` – `packages/frontend-api/src/Model/Resolver/Transport/TransportsQuery.php:101-144`
  6. `Variant.mainVariant` pod entitním rodičem – fallback `FieldResolver::valueFromObjectOrArray` → `Product::getMainVariant()` bez filtru viditelnosti
- **Kód mimo mappery, který zvládá entitu i pole:** `ProductPriceQuery` (`priceByProductQuery`, `giftPriceByProductQuery`, `isProductUponInquiry`), `ProductImagesQuery`, `ProductImagesCountQuery`, `ProductFilesQuery`, `ProductGiftsQuery`, `ProductReviewsQuery::getProductIdCarryingReviews`, `packages/product-feed-zbozi/src/FrontendApi/Resolver/ZboziCategoryQuery.php`.
- **ES index** obsahuje produkt právě tehdy, když je viditelný aspoň pro jednu cenovou skupinu domény (`packages/framework/src/Model/Product/Elasticsearch/ProductExportRepository.php:264-266`). Selling-denied / vyprodané produkty v indexu jsou (filtr až při dotazu). Smazané a úplně neviditelné produkty v indexu nejsou. Export je asynchronní, ES může krátce zaostávat za DB.
- **Existující DataLoadery** (`packages/frontend-api/src/Model/Product/BatchLoad/ProductsBatchLoader.php`) mají jako klíč pole ID a jako výsledek seznam; pro jedno ID se používá `load([$id])->then(fn ($p) => $p[0] ?? null)` (`ProductReviewResolverMap.php:53-61`, `ProductArrayFieldMapper::getMainVariant`), což v `msearch` znamená jeden subdotaz na každé ID.

## Desired End State

- Každé resolvování typu `Product` dostane ES pole. `ProductEntityFieldMapper` (package i App) neexistuje, `ProductResolverMap` nevětví podle `instanceof Product`, App override `ProductResolverMap` je zrušen.
- Každé pole typu `Product` (i budoucí) automaticky převádí vrácenou entitu na ES pole – listener `ProductFieldsTypeDecoratorListener`, žádné resolvery pro jednotlivá místa.
- Produkty navázané na objednávku / košík / reklamaci se načítají jedním ES requestem (`msearch`) na GraphQL request přes existující `products_visible_by_ids_batch_loader`, bez N+1.
- Produkt, který v ES pro aktuální cenovou skupinu není (skrytý, smazaný, nedoexportovaný):
  - nullable pole (`OrderItem.product`, `ComplaintItem.product`, `Variant.mainVariant`) → `null`,
  - seznamy produktů (`notAddedProducts`, produkty blokující dopravu) → produkt se vynechá,
  - košíkové seznamy: skryté produkty se do `noLongerListableCartItems` / `notAddedProducts` vůbec nedostanou (rozhodnutí v doméně podle DB viditelnosti), místo toho se nastaví `someProductWasRemovedFromEshop = true`,
  - `CartItem.product` při ES lagu (DB produkt vidí, ES ještě ne) → GraphQL chyba (zaloguje se), vědomě akceptováno.
- GraphQL schéma se nemění.
- Storefront v objednávkách/reklamacích bere popisná data ze snapshotu položky, ne z produktu, a nespoléhá na existenci produktu.
- Máme změřená čísla výkonu košíku (a detailu objednávky) před a po změně.

### Key Discoveries:

- Loader pattern + registrace: `project-base/app/config/packages/overblog_dataloader.yaml:62-88`, injektování aliasem v `packages/frontend-api/src/Resources/config/services.yaml:214-243`.
- `products_visible_by_ids_batch_loader` → `ProductsBatchLoader::loadVisibleByIds()` → `ProductElasticsearchBatchProvider::getBatchedVisibleByProductIds()`: každý klíč dávky = jeden subdotaz v jednom `msearch` (`createVisibleProductsByProductIdsFilter`, filtr viditelnosti pro aktuální cenovou skupinu, pořadí podle ID, chybějící ID vypadnou).
- Vzor jednoho produktu přes tento loader: `ProductReviewResolverMap::loadVisibleProduct()` (`load([$id])->then(fn ($p) => $p[0] ?? null)`).
- `MultipleSearchQuery` (`packages/frontend-api/src/Component/Elasticsearch/MultipleSearchQuery.php:36-46`) nevylučuje `reviews` ze `_source` – u `msearch` se tahají i recenze; relevantní pro měření ve fázi 6.
- Overblog (v1.10) vyvolá po vytvoření každého typu event `graphql.type_loaded`; `TypeDecoratorListener` (`vendor/overblog/graphql-bundle/src/EventListener/TypeDecoratorListener.php`, registrace `vendor/overblog/graphql-bundle/src/Resources/config/listeners.yaml:8-12`, priorita 0) na něm obalí thunk `$type->config['fields']` a doplní resolvery z resolver map. Listener s nižší prioritou může stejnou technikou obalit už finální resolvery.
- Dohledání efektivního resolveru pole (vlastní `resolve` → `resolveField` typu → default resolver): `AclConfigProcessor::findFieldResolver()` (`vendor/overblog/graphql-bundle/src/Definition/ConfigProcessor/AclConfigProcessor.php`), default resolver je služba `overblog_graphql.default_field_resolver`.
- GraphQL propaguje `null` v non-null poli na nejbližšího nullable rodiče – u `CartItem.product: Product!` v `[CartItem!]!` by to zahodilo celý seznam, proto se skryté produkty z košíkových seznamů řeší v doméně (fáze 2).
- `CartWithModificationsResult::setCartHasRemovedProducts()` (`:161`) nastavuje `someProductWasRemovedFromEshop`.
- `CartWatcherFacade::checkNotListableItems()` (`:89-99`) dává do `noLongerListableCartItems` i produkty inquiry / selling-denied / s nulovou cenou – ty v ES jsou, takže zůstanou (existující test `CartModificationsResultTest::testNoLongerListableCartItemIsReported` používá `sellingDenied` → beze změny).
- Storefront už má obecný toast pro `someProductWasRemovedFromEshop` (`project-base/storefront/connectors/cart/Cart.ts:38`); `NotAddedProductsPopup` má obecný titulek, funguje i s prázdným seznamem.
- Vazba `product` na `OrderItem` (`packages/framework/src/Model/Order/Item/OrderItem.php:169-170`), `CartItem` (`packages/framework/src/Model/Cart/Item/CartItem.php:47-48`) i `ComplaintItem` je `ManyToOne` s lazy fetch a FK `product_id` v řádku rodiče → `getProduct()` vrací neinicializovaný proxy s vyplněným ID; `instanceof Product` platí a `getId()` proxy neinicializuje (ORM 3.7.1, PHP 8.5 – lazy ghost i nativní lazy objekty nastavují identifikátor bez inicializace). Listener tak u objednávek a reklamací produkt z DB vůbec nenačte. V košíku jsou produkty načtené už `CartWatcherFacade` (ceny, viditelnost, sklad), DB dotaz navíc tedy nevznikne. Smazaný produkt: `onDelete: SET NULL` → `getProduct()` vrací `null`, visící proxy nehrozí.
- Profiler obsahuje Doctrine i Elasticsearch collector (`packages/framework/src/Component/Elasticsearch/Debug/ElasticsearchCollector.php`) – použijeme pro měření.

## What We're NOT Doing

- Snapshot obrázku, kategorie ani typu produktu na `OrderItem` / `ComplaintItem` (obrázky se berou jen z ES produktu → u skrytých produktů placeholder).
- Načítání produktů bez filtru viditelnosti (z neviditelných produktů na SF nic nebereme).
- Změny GraphQL schématu (žádná nová pole, žádná změna nullability).
- Oprava `MultipleSearchQuery`, který nevylučuje `reviews` ze `_source` (samostatný ticket).
- Nový jednopoložkový DataLoader (jeden ES dotaz se všemi ID místo subdotazu na každé ID) – doplníme jen tehdy, pokud měření ve fázi 6 ukáže, že `msearch` s desítkami subdotazů je drahý.
- Úklid nepoužívaných selekcí v `OrderDetailItemFragment` (`catalogNumber`, `categories`, `availability`, `promotion*Quantity`).

## Implementation Approach

1. Nejdřív změřit baseline výkonu na `20.0` (fáze 0), dokud kód není změněný.
2. Přidat listener, který obalí resolvery všech polí typu `Product` a vrácené entity převede přes `products_visible_by_ids_batch_loader` na ES pole.
3. Skryté produkty v košíkových seznamech vyřešit v doméně (`CartWatcherFacade`, `CartMutation`).
4. Odstranit entitní cestu a vše, co tím osiřelo.
5. Testy, upgrade notes, docs.
6. Storefront opravy, každá v samostatném commitu.
7. Změřit výkon na větvi a porovnat.

Commity: BE (`FE API:` prefix) a SF (`SF:` prefix) odděleně; plán jen v `docs:` commitech.

---

## Phase 0: Baseline měření výkonu na 20.0

### Overview

Změřit výkon `CartQuery` (a bonusově `OrderDetailQuery`) s větším počtem položek **před** jakoukoli změnou kódu. Stejný postup se zopakuje ve fázi 6.

### Postup

1. Ověřit, že checkout je na `20.0` bez lokálních změn, smazat `var/cache/dev` (`docker compose exec php-fpm rm -rf var/cache/dev`).
2. Připravit data (jednou, sdílí se pro obě měření – mezi fázemi 0 a 6 **nedělat** `db-rebuild` / `db-demo`):
   - Načíst N prodejných produktů dotazem `products(first: 60) { edges { node { uuid } } }` (doména 1, anonymní uživatel).
   - Založit 3 anonymní košíky přes `AddToCart` (bez `cartUuid` v prvním volání, pak s vráceným `uuid`) s **N = 10, 30, 60** různými produkty. Uložit `cartUuid` do souboru ve scratchpadu.
   - Bonus: z košíku s 60 položkami vytvořit objednávku (`CreateOrder`) a uložit její `uuid` + `urlHash` pro `OrderDetailByHashQuery` (pozor: vytvoření objednávky košík spotřebuje → na objednávku použít čtvrtý košík s 60 položkami).
3. Dotaz: přesně storefrontový `CartQuery` (`project-base/storefront/graphql/requests/cart/queries/CartQuery.graphql` + rekurzivně všechny použité fragmenty ze `graphql/requests/**/fragments/*.graphql`), složený pomocným skriptem ve scratchpadu do jednoho dokumentu. Totéž pro `OrderDetailByHashQuery`.
4. Metriky pro každé N:
   - **Počet a čas DB dotazů, počet ES requestů**: jeden request s hlavičkou, která vrátí `X-Debug-Token`; profil načíst pomocným PHP skriptem v kontejneru (služba `profiler`, `loadProfile($token)` → collector `db`: `getQueryCount()`, `getTime()`; Elasticsearch collector: počet requestů a čas).
   - **Čas odpovědi**: 3× warm-up, pak 20 měřených requestů `curl -s -o /dev/null -w '%{time_total}'`; zapsat medián a p95.
   - Měří se v dev prostředí (běžící kontejnery nepřepínáme); overhead profileru je na obou stranách stejný, porovnáváme relativně.
5. Výsledky zapsat do tabulky v sekci [Performance Considerations](#performance-considerations) tohoto plánu (sloupec „20.0“). Pomocné skripty si nechat ve scratchpadu pro fázi 6.

### Success Criteria:

#### Automated Verification:

- [ ] Pro N = 10/30/60 existují čísla: DB dotazy (počet, čas), ES requesty, medián a p95 doby odpovědi `CartQuery`
- [ ] Bonus: totéž pro `OrderDetailByHashQuery` s 60 položkami

#### Manual Verification:

- [ ] Košíky a objednávka zůstávají v DB pro fázi 6

---

## Phase 1: Automatické donačítání produktů z ES pro všechna pole typu Product

### Overview

Žádné resolvery na jednotlivých místech. Jeden listener na overblog eventu `graphql.type_loaded` obalí resolver **každého pole, jehož návratový typ je `Product` / `RegularProduct` / `Variant` / `MainVariant`** (samostatně i jako seznam). Obal vezme výsledek původního resolveru a entitu (nebo seznam entit) převede přes existující `products_visible_by_ids_batch_loader` na ES pole. Tím se automaticky pokryjí `OrderItem.product`, `ComplaintItem.product`, `CartItem.product`, `notAddedProducts`, produkty blokující dopravu, `Variant.mainVariant` i budoucí pole – bez úprav jejich resolverů.

Jak to v overblogu funguje:
- Overblog po vytvoření každého typu vyvolá `graphql.type_loaded`; jeho `TypeDecoratorListener` (`vendor/overblog/graphql-bundle/src/EventListener/TypeDecoratorListener.php`, priorita 0) na něm doplní do `$type->config['fields']` resolvery z resolver map (obalí thunk `fields`).
- Náš listener s nižší prioritou poběží až po něm, takže uvidí finální `resolve` každého pole a obalí ho stejnou technikou (obalení thunku `fields`).
- Pokud pole vlastní `resolve` nemá, původní resolver se dohledá stejně jako v `AclConfigProcessor::findFieldResolver()` (`vendor/overblog/graphql-bundle/src/Definition/ConfigProcessor/AclConfigProcessor.php`): `$field['resolve']` → `$type->config['resolveField']` (např. `RESOLVE_FIELD` z `ProductResolverMap` pro `Variant.mainVariant`) → `@overblog_graphql.default_field_resolver`.
- O tom, které pole se obalí, se rozhoduje jednou při stavbě typu; za běhu se navíc volá jen normalizace výsledku u produktových polí.

### Changes Required:

#### 1. Listener

**File**: `packages/frontend-api/src/Model/Resolver/Products/ProductFieldsTypeDecoratorListener.php` (nová; název/umístění ověřit proti konvencím, např. `Component/` vs. `Model/Resolver/`)

```php
class ProductFieldsTypeDecoratorListener
{
    /**
     * @var string[]
     */
    protected array $productTypeNames = ['Product', 'RegularProduct', 'Variant', 'MainVariant'];

    /**
     * @param callable $defaultFieldResolver
     */
    public function __construct(
        protected readonly DataLoaderInterface $productsVisibleByIdsBatchLoader,
        protected readonly mixed $defaultFieldResolver,
    ) {
    }

    public function onTypeLoaded(TypeLoadedEvent $event): void
    {
        $type = $event->getType();

        if (!$type instanceof ObjectType) {
            return;
        }

        $fields = $type->config['fields'];

        $type->config['fields'] = function () use ($fields, $type) {
            $fields = is_callable($fields) ? $fields() : $fields;
            $fields = $fields instanceof Traversable ? iterator_to_array($fields) : (array)$fields;

            foreach ($fields as &$field) {
                if (!$this->isProductField($field)) {
                    continue;
                }

                $originalResolver = $field['resolve'] ?? $type->config['resolveField'] ?? $this->defaultFieldResolver;
                $field['resolve'] = fn (...$args) => $this->normalizeResolvedValue($originalResolver(...$args));
            }

            return $fields;
        };
    }

    protected function isProductField(mixed $field): bool
    {
        // $field['type'] can be a Type instance or a thunk; unwrap NonNull/List via Type::getNamedType()
    }

    /**
     * Product entities are replaced by their Elasticsearch arrays; products not visible for the current customer
     * become null (single value) or are omitted (list)
     */
    protected function normalizeResolvedValue(mixed $value): mixed
    {
        if ($value instanceof Promise) {
            return $value->then(fn ($resolvedValue) => $this->normalizeResolvedValue($resolvedValue));
        }

        if ($value instanceof Product) {
            return $this->productsVisibleByIdsBatchLoader->load([$value->getId()])
                ->then(static fn (array $products) => $products[0] ?? null);
        }

        if (is_array($value) && array_is_list($value) && ($value[0] ?? null) instanceof Product) {
            // the loader keeps the order of the IDs and omits products that are not visible
            return $this->productsVisibleByIdsBatchLoader->load(array_map(static fn (Product $product) => $product->getId(), $value));
        }

        return $value;
    }
}
```

Poznámky k implementaci:
- Listener smí na entitě volat **jen `getId()`** – cokoli jiného inicializuje lazy proxy a vrátí DB dotazy, kterých se zbavujeme (proto také musí zmizet entitní větev v `RESOLVE_TYPE`, která volá `isMainVariant()`).
- Zkontrolovat ostatní pole na stejných rodičích, která sahají na produkt, zda proxy neinicializují – např. `OrderItem.mainImage` (`packages/frontend-api/src/Model/Resolver/Image/OrderItemImagesQuery.php:26` volá `getProduct()`); pokud z produktu potřebují jen ID, je to v pořádku.
- ES pole produktu je asociativní pole (`array_is_list` = false) a seznam ES polí nemá na indexu 0 entitu → obojí projde beze změny.
- Ověřit, jaký typ promise vrací resolvery (`GraphQL\Executor\Promise\Promise` z DataLoaderu přes webonyx sync adapter); případně ošetřit i `SyncPromise`.
- Ověřit, že obalení thunku `fields` po `TypeDecoratorListener` funguje i pro typy, na které žádná resolver mapa nesahá (thunk je jen jeden callable, obalujeme ho znovu).
- `$field['type']` nevyhodnocovat mimo thunk `fields` (typy se načítají líně, předčasné načtení by mohlo vést k cyklu).
- Argumenty se předávají beze změny: `resolve` z resolver map je už obalený `ArgumentFactory::wrapResolverArgs()` a očekává surové argumenty webonyx.

#### 2. Registrace

**File**: `packages/frontend-api/src/Resources/config/services.yaml`

```yaml
Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductFieldsTypeDecoratorListener:
    arguments:
        $productsVisibleByIdsBatchLoader: '@products_visible_by_ids_batch_loader'
        $defaultFieldResolver: '@overblog_graphql.default_field_resolver'
    tags:
        # must run after overblog's TypeDecoratorListener (priority 0) has applied the resolver maps
        - { name: kernel.event_listener, event: graphql.type_loaded, method: onTypeLoaded, priority: -10 }
```

#### 3. Unit test

**File**: `packages/frontend-api/tests/Unit/…/ProductFieldsTypeDecoratorListenerTest.php` (umístění podle existujících unit testů frontend-api)
- produktové pole s resolverem vracejícím entitu → volá loader s `[$id]`, vrací ES pole / `null`,
- seznam entit → jeden `load($ids)`,
- promise s entitou → normalizuje se po splnění,
- ES pole, seznam ES polí a `null` projdou beze změny,
- neproduktové pole se neobalí,
- pole bez vlastního `resolve` použije `resolveField` typu, jinak default resolver.

### Success Criteria:

#### Automated Verification:

- [ ] Kontejner se sestaví: `docker compose exec php-fpm php bin/console cache:clear`
- [ ] Unit test listeneru prochází
- [ ] Funkční testy objednávek, košíku, reklamací a dopravy procházejí: `docker compose exec php-fpm php phing tests-functional`
- [ ] `docker compose exec php-fpm php phing standards-fix` (+ phpstan dle `shopsys-commands`)

#### Manual Verification:

- [ ] Detail objednávky s 10+ položkami: v profileru jeden ES request (`msearch`) pro produkty položek, žádný N+1, `ProductEntityFieldMapper` se už nevolá (breakpoint / log)
- [ ] Detail objednávky a detail reklamace: v Doctrine panelu profileru žádné dotazy na tabulku `products` (ani jejích překladů / domén) – proxy produktů zůstávají neinicializované

---

## Phase 2: Skryté produkty v košíkových seznamech (doménová úprava)

### Overview

Listener převede nevyhovující produkt na `null` / vynechá ho ze seznamu produktů. U polí `CartItem.product: Product!` by ale `null` propagací zahodilo celý seznam `[CartItem!]!` (a s ním košík). Proto skryté produkty do košíkových seznamů vůbec nedáme – rozhodne se v doméně podle DB viditelnosti, ještě před GraphQL. Žádné resolvery navíc.

### Changes Required:

#### 1. noLongerListableCartItems (B2)

**File**: `packages/frontend-api/src/Model/Cart/CartWatcherFacade.php` (`checkNotListableItems`, `:89-99`)
**Changes**: položku, jejíž produkt není viditelný pro aktuální cenovou skupinu (`ProductVisibilityFacade::getProductVisibility(...)->isVisible()`, stejný dotaz jako v `CartWatcher::getNotListableItems`), z košíku odebrat jako dosud, ale místo `addNoLongerListableCartItem()` zavolat `setCartHasRemovedProducts()`. Inquiry / selling-denied / nulová cena se dál hlásí v `noLongerListableCartItems` (tyto produkty v ES jsou).
- Doplnit závislost `ProductVisibilityFacade` (+ `Domain`, pokud chybí). Dotaz navíc jen pro neprodejné položky (výjimečné).
- Alternativa k ověření při implementaci: nechat `CartWatcher` vracet i důvod, aby se viditelnost nepočítala dvakrát – zvolit jednodušší variantu.

#### 2. notAddedProducts (B5)

**File**: `packages/frontend-api/src/Model/Mutation/Cart/CartMutation.php` (`addOrderItemsToCartMutation`, `:123-138`)
**Changes**: při `InvalidCartItemUserError` zkontrolovat viditelnost produktu; skrytý produkt nepřidávat do `$notAddedProducts`, místo toho po `getCheckedCartWithModifications()` zavolat `setCartHasRemovedProducts()`. Viditelné (selling-denied, nedostatek skladu, …) dál do `notAddedProducts`.
- Opravit docblock `CartWithModificationsResult::$multipleAddedProductModifications` (dnes chybně `array<string, array<int, string>>`).

#### 3. Ostatní místa bez úprav

- `Cart.items` a zbylé modification listy: skryté produkty odstraní watcher předem (`checkNotListableItems`), `CartItem.product` dostane ES pole přes listener.
- `ProductsByTransportUnavailabilityReason.products`: seznam entit → listener vrátí jen viditelné; skupina zůstává i s prázdným seznamem (doprava zůstane zablokovaná, jen bez výpisu produktů).
- `OrderItem.product`, `ComplaintItem.product`, `Variant.mainVariant`: nullable, listener vrátí `null`.
- **Zbývající hrana – ES lag:** DB produkt vidí, ES ještě ne → `CartItem.product` dostane `null` → GraphQL chyba „Cannot return null for non-nullable field“. Vědomě akceptováno (krátkodobý stav do doběhnutí exportu, zaloguje se).

### Success Criteria:

#### Automated Verification:

- [ ] `docker compose exec php-fpm php phing tests-functional` – cart testy procházejí (existující `CartModificationsResultTest::testNoLongerListableCartItemIsReported` se selling-denied produktem beze změny)
- [ ] `docker compose exec php-fpm php phing standards-fix` + phpstan

---

## Phase 3: Zrušení entitní cesty a úklid

### Changes Required:

#### 1. ProductResolverMap

- `packages/frontend-api/src/Model/Resolver/Products/ProductResolverMap.php`: odstranit `ProductEntityFieldMapper` z konstruktoru, `mapProduct()` vždy používá `$this->productArrayFieldMapper`; `RESOLVE_TYPE` jen z pole (`is_main_variant`, `main_variant_id`).
- `project-base/app/src/FrontendApi/Resolver/Products/ProductResolverMap.php`: smazat (override je po změně identický s rodičem); smazat alias v `project-base/app/config/services.yaml:461-462`. Typy App mapperu zajistí alias `ProductArrayFieldMapper` (`:455`).

#### 2. Mappery

- Smazat `packages/frontend-api/src/Model/Resolver/Products/DataMapper/ProductEntityFieldMapper.php` a `project-base/app/src/FrontendApi/Resolver/Products/DataMapper/ProductEntityFieldMapper.php` + definice služeb (`packages/frontend-api/src/Resources/config/services.yaml:237-243`, `project-base/app/config/services.yaml:104-113`, alias `:458-459`).
- `App\…\ProductArrayFieldMapper`: přidat `getName(array $data): string` → `$data['name'] ?? ''` (`name: String!` v project-base; entitní mapper dělal `?? ''`).

#### 3. Resolvery s dvojím tvarem → jen pole

Parametr `Product|array` → `array`, smazat entitní větve a nepoužité importy / závislosti:

- `packages/frontend-api/src/Model/Resolver/Price/ProductPriceQuery.php` – odstranit `SpecialPriceFacade`, `ProductCachedAttributesFacade` z konstruktoru, `ProductPriceMissingUserError` throw (jen entitní větev).
- `packages/frontend-api/src/Model/Resolver/Image/ProductImagesQuery.php`, `ProductImagesCountQuery.php`
- `packages/frontend-api/src/Model/Resolver/Products/ProductFilesQuery.php`, `ProductGiftsQuery.php`
- `packages/frontend-api/src/Model/Resolver/ProductReview/ProductReviewsQuery.php` (`getProductIdCarryingReviews`)
- `packages/product-feed-zbozi/src/FrontendApi/Resolver/ZboziCategoryQuery.php` (odstranit `ZboziCategoryFacade` z resolveru, pokud jinak nepoužitý; facade metoda zůstává pro feed)

#### 4. Osiřelé loadery a metody

- `product_slug_batch_loader` (`overblog_dataloader.yaml:65-67`) + `FriendlyUrlSlugBatchLoader::loadProductSlugs()`
- `additional_services_by_product_id_batch_loader` (`overblog_dataloader.yaml:8-10`) + `AdditionalServicesBatchLoader::loadByProductIds()` a `getProductsWithAdditionalServicesIndexedById()` (ponechat `getProductsIndexedById`)
- `ProductReviewApiFacade::getReviewSummaryForProduct()` + závislost `ProductElasticsearchProvider`
- `ProductCachedAttributesFacade::getProductBasicPrice()`
- `ProductAccessoryFacade::getOfferedAccessories()`
- Před smazáním každé metody ověřit grepem napříč `packages/`, `project-base/`, `utils/` (vč. testů), že ji nic jiného nevolá; testy jen na smazané metody smazat.

#### 5. Docs

`docs/frontend-api/introduction-to-frontend-api.md:262-277` (sekce ProductResolverMap) – přepsat: data produktu jsou vždy z ES; resolver jiného typu může do pole typu `Product` vrátit entitu (nebo seznam entit) a `ProductFieldsTypeDecoratorListener` ji automaticky převede na ES pole přes `products_visible_by_ids_batch_loader` (neviditelný produkt → `null` / vynechán ze seznamu). Dokumentaci v `project-base/.agents/skills/codebase-pattern-finder/SKILL.md` zkontrolovat a případně upravit.

### Success Criteria:

#### Automated Verification:

- [ ] `grep -rn "ProductEntityFieldMapper" packages project-base docs --include=*.php --include=*.yaml --include=*.md` nic nenajde (kromě upgrade notes)
- [ ] `grep -rn "instanceof Product\b" packages/frontend-api/src packages/product-feed-zbozi/src/FrontendApi` nenajde nic v resolverech typu Product (jediný výskyt je `ProductFieldsTypeDecoratorListener`)
- [ ] Smazat `var/cache/test` a spustit `docker compose exec php-fpm php phing tests-functional tests-unit`
- [ ] `make generate-schema` nevytvoří diff ve `schema.graphql` (schéma se nemění)
- [ ] `docker compose exec php-fpm php phing standards-fix` + phpstan (pozor: nikdy `-u root`)

---

## Phase 4: Testy a upgrade notes

### Changes Required:

#### 1. Funkční GraphQL testy (`project-base/app/tests/FrontendApiBundle/Functional/`)

Skrytí produktu po objednání: `productData->hidden = true` + `handleDispatchedRecalculationMessages()` (vzor `CartModificationsResultTest.php:495-502`).

- `Order/GetOrderItemsTest` (nebo nový test): položka objednávky se skrytým produktem → `product: null`, `name`/`catnum`/ceny ze snapshotu zůstanou.
- `Cart/CartModificationsResultTest`: produkt v košíku se skryje → `noLongerListableCartItems` ho neobsahuje, `someProductWasRemovedFromEshop: true`, `items` ho neobsahuje. Existující `testNoLongerListableCartItemIsReported` (selling denied) beze změny.
- `Cart/AddOrderItemsToCartTest`: objednávka se skrytým produktem → `notAddedProducts` je prázdné, `someProductWasRemovedFromEshop: true`.
- Reklamace: `ComplaintItem.product` je `null` pro skrytý produkt.
- Test N+1 není potřeba automatizovat (ověří se profilerem ve fázi 6).

#### 2. Upgrade notes

Vygenerovat přes `/generate-upgrade-notes`; jen BC dopady:

- odstraněný `ProductEntityFieldMapper` (package) a App override `ProductResolverMap` – projekty přenesou vlastní metody z App entity mapperu do `ProductArrayFieldMapper` (data musí být v ES exportu),
- změněná signatura `Product|array` → `array` v resolverech z fáze 3,
- odstraněné loadery `product_slug_batch_loader`, `additional_services_by_product_id_batch_loader` a metody facad,
- typ `Product` dostává vždy ES pole; entity vrácené vlastními resolvery projektů převede listener automaticky, ale projektové resolvery, které s entitou pracují uvnitř typu `Product` (vlastní pole na `Product` s `resolve: value`), musí počítat s polem,
- změna chování: produkty skryté pro aktuálního zákazníka se u `OrderItem.product` / `ComplaintItem.product` vrací jako `null`, ze seznamů produktů se vynechají a místo hlášení v `noLongerListableCartItems` / `notAddedProducts` se nastaví `someProductWasRemovedFromEshop`.

### Success Criteria:

#### Automated Verification:

- [ ] Nové testy procházejí: `docker compose exec php-fpm php vendor/bin/phpunit tests/FrontendApiBundle/Functional/Cart tests/FrontendApiBundle/Functional/Order tests/FrontendApiBundle/Functional/Complaint`

---

## Phase 5: Storefront (samostatné commity)

Každý bod = vlastní `SF:` commit. Po změnách `docker compose exec storefront pnpm run typecheck`, biome, vitest.

#### S1 – seznam objednávek
`components/Pages/Customer/Orders/OrderItemProducts.tsx:25-46`: neskipovat položku při `product === null`; klíč `orderItem.uuid` (doplnit `uuid` a `name` do `OrderItemFragment`, pokud chybí), název/tooltip ze `orderItem.name`, obrázek `product?.mainImage` (jinak placeholder), odkaz jen při `product?.isVisible`. Upravit `vitest/components/Pages/Customer/Orders/OrderItemProducts.test.tsx`.

#### S2 – potvrzení objednávky
`components/Pages/OrderConfirmation/OrderConfirmationProducts.tsx:34-61`: vykreslovat položky typu Product / ProductGift i bez produktu; cena ze snapshotu `unitPrice` / `totalPrice` položky místo živé `product.price` / `product.giftPrice` (opravuje zobrazení dnešní ceny), název z `item.name`, obrázek `product?.mainImage`.

#### S4 – reklamace elektronického dárkového poukazu
`components/Pages/Customer/OrderDetail/OrderDetailOrderItem.tsx:65`: `orderItem.product !== null && orderItem.product.productType !== TypeProductTypeEnum.ElectronicGiftVoucher` (bez produktu tlačítko skrýt). Upravit `vitest/.../OrderDetailOrderItem.test.tsx`.

#### B5 – opakování objednávky
`utils/cart/useAddOrderItemsToCart.tsx:44-45`: `addedAllProducts = notAddedProducts.length === 0 && !newCart.modifications.someProductWasRemovedFromEshop`. Popup (`NotAddedProductsPopup`) s prázdným seznamem názvů zobrazí jen obecný titulek – ověřit vzhled.

### Success Criteria:

#### Automated Verification:

- [ ] `docker compose exec storefront pnpm run typecheck`
- [ ] `docker compose exec storefront pnpm run lint` (biome) a `pnpm run test` (vitest)

#### Manual Verification:

- [ ] Seznam objednávek, detail objednávky, potvrzení objednávky a reklamace (detail, nová reklamace) s produktem, který byl po objednání **skryt** i **smazán**: položka se zobrazí se jménem, katalogovým číslem a cenou ze snapshotu, bez odkazu, s placeholder obrázkem, nic nespadne
- [ ] Reklamační tlačítko se u položky bez produktu nezobrazí
- [ ] Košík: skrytí produktu v košíku → obecný toast „Some product was removed…“, položka zmizí
- [ ] Opakování objednávky se skrytým produktem → zobrazí se popup, ne tiché přesměrování
- [ ] Výběr dopravy s produktem blokujícím dopravu funguje beze změny

---

## Phase 6: Porovnání výkonu

### Overview

Zopakovat měření z fáze 0 na větvi po přepisu a porovnat.

### Postup

1. `docker compose exec php-fpm rm -rf var/cache/dev`, ověřit, že ES index je aktuální (žádné nezpracované recalculation zprávy).
2. Pustit stejné skripty se stejnými `cartUuid` / objednávkou (warm-up request zároveň projde cart watcherem).
3. Doplnit sloupec „větev“ v tabulce níže a spočítat rozdíl. Čísla použít v PR description.

### Success Criteria:

#### Automated Verification:

- [ ] Tabulka vyplněná pro N = 10/30/60 (+ bonus objednávka)

#### Manual Verification:

- [ ] Počet DB dotazů klesá s počtem položek (u 20.0 roste lineárně s N, u větve by měl být přibližně konstantní pro produktová pole)
- [ ] Počet ES requestů pro produkty je 1 (`msearch`) na request bez ohledu na N
- [ ] `OrderDetailByHashQuery` na větvi nespouští žádné dotazy na tabulku `products` (porovnat s 20.0, kde se produkty položek inicializují); u `CartQuery` zůstávají jen dotazy z `CartWatcherFacade`
- [ ] Čas ES (Elasticsearch collector) roste s N přijatelně; pokud je `msearch` s N subdotazy výrazně dražší než DB úspora, zvážit jednopoložkový loader / vyloučení `reviews` ze `_source` (viz What We're NOT Doing)

---

## Testing Strategy

### Unit Tests:

- `ProductFieldsTypeDecoratorListener`: normalizace entity, seznamu entit, promise, průchod ES polí a `null`, výběr původního resolveru (fáze 1).

### Functional Tests:

- Položka objednávky / reklamace se skrytým produktem → `product: null`.
- Košík se skrytým produktem → položka není v `noLongerListableCartItems` ani `items`, `someProductWasRemovedFromEshop: true`.
- Opakování objednávky se skrytým produktem → prázdné `notAddedProducts` + flag.
- Stávající testy košíku, objednávek, recenzí, variant a dopravy musí projít beze změny očekávaných dat (kromě případů, kdy test výslovně počítal s živými daty entity – takové projít a rozhodnout jednotlivě).

### Manual Testing Steps:

1. Vytvořit objednávku se 3 produkty, jeden poté v adminu skrýt a jeden smazat.
2. Zkontrolovat seznam objednávek, detail objednávky, potvrzení objednávky (přes URL hash), novou reklamaci a detail reklamace.
3. Kliknout na „Opakovat objednávku“ → popup.
4. Vložit produkt do košíku, v adminu ho skrýt, obnovit košík → toast a položka zmizí.
5. Porovnat zobrazení produktů v košíku s 20.0 (dostupnost, cena, odkaz, flagy) – data teď jdou z ES.

## Performance Considerations

Očekávání: entitní mapper dělal pro každou položku košíku/objednávky samostatné DB dotazy (dostupnost a sklad, viditelnost, flagy vč. variant, link, hreflangy, parametry, cena přes cached attributes + special price) a samostatné ES volání pro `reviewsSummary`. To nahradí jeden ES request (`msearch` s jedním subdotazem na každý produkt, `_source` včetně `reviews`). Přepočet košíku v `CartWatcherFacade` dál pracuje s entitami, takže část DB dotazů zůstane. Zlepšení by mělo růst s počtem položek.

Výsledky (vyplní se ve fázi 0 a 6):

| Dotaz | N | DB dotazy 20.0 | DB dotazy větev | DB čas 20.0 | DB čas větev | ES req. 20.0 | ES req. větev | medián 20.0 | medián větev | p95 20.0 | p95 větev |
|---|---|---|---|---|---|---|---|---|---|---|---|
| CartQuery | 10 | | | | | | | | | | |
| CartQuery | 30 | | | | | | | | | | |
| CartQuery | 60 | | | | | | | | | | |
| OrderDetailByHashQuery | 60 | | | | | | | | | | |

## Migration Notes

- Žádná DB migrace ani změna ES mappingu.
- Projekty s vlastními metodami v `App\…\ProductEntityFieldMapper` je musí mít v `ProductArrayFieldMapper` (a data v ES exportu) – popsáno v upgrade notes.

## References

- Jira: SSP-4418
- Použitý loader: `packages/frontend-api/src/Model/Product/BatchLoad/ProductsBatchLoader.php:30` (`loadVisibleByIds`)
- Precedens produktu z loaderu v resolver mapě: `packages/frontend-api/src/Model/Resolver/ProductReview/ProductReviewResolverMap.php:43,53-61`
- Vzor obalení polí typu: `vendor/overblog/graphql-bundle/src/EventListener/TypeDecoratorListener.php` (`decorateObjectTypeFields`), dohledání resolveru: `vendor/overblog/graphql-bundle/src/Definition/ConfigProcessor/AclConfigProcessor.php` (`findFieldResolver`)
- Původní rozdělení mapperů: commit fea4d2545e
