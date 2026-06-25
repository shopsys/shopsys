# Automatický doménový filtr pro CRUD controller – Implementační plán

## Overview

Rozšířit CRUD vrstvu balíku `packages/administration` tak, aby list page **automaticky** rozpoznala, zda je entita doménová, a podle toho:

1. nad gridem vykreslila přepínač/filtr domén,
2. vyfiltrovala položky gridu podle vybrané domény.

Detekce typu entity probíhá z Doctrine metadat (tři situace: bez domén / skalární `domainId` / `OneToMany $domains`). Chování lze ručně přebít přes `CrudConfig`. Výchozí facade režim je **SWITCH** (globálně vybraná jedna doména přes [`AdminDomainTabsFacade`](../../packages/framework/src/Component/Domain/AdminDomainTabsFacade.php)), volitelně lze přepnout na **FILTER** (per-page filtr s volbou „All domains“ přes [`AdminDomainFilterTabsFacade`](../../packages/framework/src/Component/Domain/AdminDomainFilterTabsFacade.php)).

## Current State Analysis

- CRUD controllery dědí z [`AbstractCrudController`](../../packages/administration/src/Controller/AbstractCrudController.php). List page se staví v `listAction()` ([:82](../../packages/administration/src/Controller/AbstractCrudController.php:82)):
  `OrmAdapterFactory::create($entityClass, $configureQuery)` → `DatagridFactory::create()` → render `@ShopsysAdministration/crud/list.html.twig`.
- `configureQuery(QueryBuilder)` ([:71](../../packages/administration/src/Controller/AbstractCrudController.php:71)) je jediný hook na úpravu dotazu; closure v `listAction` skládá controllerový `configureQuery` + extensions.
- Root alias dotazu je **vždy `'o'`** ([`ProxyQuery::DEFAULT_ALIAS`](../../packages/administration/src/Component/Datagrid/Adapter/Orm/ProxyQuery.php:16)).
- Servisy se do controlleru injektují přes `#[Required]` public property (viz `datagridFactory`, `ormAdapterFactory` v `AbstractCrudController`).
- Konfigurace teče: [`CrudControllerRegistry::buildConfig()`](../../packages/administration/src/Component/Crud/CrudControllerRegistry.php:108) vytvoří `CrudConfig`, zavolá `$controller->configure($config)` + extension `configure()`, pak `$config->getConfig()` → immutable [`CrudConfigData`](../../packages/administration/src/Component/Config/CrudConfigData.php) → uloženo v [`Definition`](../../packages/administration/src/Component/Crud/Definition.php) a čteno v `listAction` přes `$this->definition->getConfig()`.
- `controllerName` (např. `OrderController`) je v `Definition` k dispozici – použijeme jako namespace pro FILTER režim.
- Šablona [`crud/list.html.twig`](../../packages/administration/templates/crud/list.html.twig) má blok `main_content` jen s `{{ grid.render }}` – **chybí hook nad grid**.
- Existující znovupoužitelné komponenty:
  - SWITCH: [`DomainController::domainTabsAction`](../../packages/framework/src/Controller/Admin/DomainController.php:39) → partial [`switch_domain.html.twig`](../../packages/administration/templates/partial/switch_domain.html.twig).
  - FILTER: [`DomainFilterController::domainFilterTabsAction(namespace)`](../../packages/framework/src/Controller/Admin/DomainFilterController.php:25) → partial [`quick_domain_filter.html.twig`](../../packages/administration/templates/partial/quick_domain_filter.html.twig) (tlačítko „All domains“, route `admin_domainfilter_selectdomain`).
  - Twig funkce `isMultidomain()` je registrovaná ([`DomainExtension`](../../packages/framework/src/Twig/DomainExtension.php:31)) a partialy si guard řeší samy.

### Key Discoveries:

- Tři tvary doménové entity (ověřeno):
  - **SCALAR**: skalární `domainId` – [`Order`](../../packages/framework/src/Model/Order/Order.php:329), [`Inquiry`](../../packages/framework/src/Model/Inquiry/Inquiry.php:35). Detekce: `ClassMetadata::hasField('domainId')`.
  - **COLLECTION**: `OneToMany $domains` → cílová entita se skalárním `domainId` – [`Category`](../../packages/framework/src/Model/Category/Category.php:102) → [`CategoryDomain`](../../packages/framework/src/Model/Category/CategoryDomain.php:39) (`ManyToOne ... inversedBy: 'domains'`). Detekce: `hasAssociation('domains')` && target `hasField('domainId')`.
  - **NONE**: nic z výše uvedeného → bez filtru.
- ORM 3 API: `ClassMetadata::getAssociationMapping()` vrací objekt s vlastnostmi `->targetEntity`, `->mappedBy`, metoda `->type()` (viz [`ProxyQuery:187,200`](../../packages/administration/src/Component/Datagrid/Adapter/Orm/ProxyQuery.php:187)). Inverzní pole na cílové entitě = `mappedBy` (u `Category` → `'category'`).
- Balík `administration` je psán striktně: `final`, `private`, plné typehints (na rozdíl od framework entit/data-objektů). Nové třídy v `administration` budou tento styl dodržovat. `AbstractCrudController` zůstává `abstract` s `protected`/`#[Required]` styly.

## Desired End State

- CRUD controller nad doménovou entitou bez jakékoli konfigurace zobrazí nad gridem přepínač domén a grid je scoped na vybranou doménu.
- Vývojář může chování přebít v `configure()`: vypnout, vynutit typ/sloupec/asociaci, přepnout facade režim.
- Pro netriviální entity je vystavené `protected ?int $selectedDomainFilterId`, takže si lze filtr napsat ručně v `configureQuery`.

Ověření: viz Success Criteria u jednotlivých fází + Testing Strategy.

## What We're NOT Doing

- Neměníme žádné stávající (ne-CRUD) admin controllery ani jejich gridy.
- Neřešíme doménově-specifické sloupce gridu (různá data per doména) – jen filtrování řádků.
- Neřešíme storefront/GraphQL – čistě admin backend + Twig.
- Nepřidáváme nové routy ani facade metody – znovupoužíváme existující `DomainController`/`DomainFilterController` a obě facades.
- Neměníme detail/edit/create/delete akce – pouze `listAction`.
- Neimplementujeme detekci podle marker interface (žádný v kódu neexistuje) – detekce je čistě z Doctrine metadat.

## Implementation Approach

Doménový filtr je **first-class feature CRUD vrstvy** (ne per-controller extension), aby fungoval automaticky. Dvě nezávislé dimenze:

1. **Typ entity** (`DomainFilterType`: NONE/SCALAR/COLLECTION) → *jak* filtrovat dotaz. Plně auto z metadat, override v configu.
2. **Facade režim** (`DomainFilterMode`: SWITCH/FILTER) → *kterou* facade použít a jaký partial vykreslit. Default SWITCH.

Detekce + aplikace filtru je vytažena do servisy `CrudDomainFilterResolver` (testovatelná, DRY). `AbstractCrudController::listAction` ji volá, obalí `configureQuery` o doménový `andWhere` (před uživatelským kódem) a do šablony předá flagy pro render partialu.

---

## Phase 1: Enumy a konfigurační API

### Overview
Definovat typy a rozšířit `CrudConfig`/`CrudConfigData` o nastavení doménového filtru (s defaultem = auto-detekce, SWITCH).

### Changes Required:

#### 1. Nový enum typu filtru
**File**: `packages/administration/src/Component/Config/DomainFilterType.php`
```php
<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

enum DomainFilterType
{
    case NONE;
    case SCALAR;
    case COLLECTION;
}
```

#### 2. Nový enum facade režimu
**File**: `packages/administration/src/Component/Config/DomainFilterMode.php`
```php
<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

enum DomainFilterMode
{
    case SWITCH;
    case FILTER;
}
```

#### 3. `CrudConfig` – fluent API
**File**: [`packages/administration/src/Component/Config/CrudConfig.php`](../../packages/administration/src/Component/Config/CrudConfig.php)
**Changes**: nové property (s defaulty = auto-detekce zapnuta, SWITCH), tři fluent metody, předání do `CrudConfigData`.
```php
private bool $domainFilterDisabled = false;

private ?DomainFilterType $domainFilterType = null; // null = auto-detekce z metadat

private string $domainFilterField = 'domainId';

private string $domainFilterAssociation = 'domains';

private DomainFilterMode $domainFilterMode = DomainFilterMode::SWITCH;

/**
 * Disable automatic domain filter even if the entity is domain-aware.
 *
 * @return $this
 */
public function disableDomainFilter(): self
{
    $this->domainFilterDisabled = true;

    return $this;
}

/**
 * Override automatic domain filter detection. Pass $type to force a specific filtering strategy
 * (e.g. for non-standard field/association names), or null to keep auto-detection with custom names.
 *
 * @return $this
 */
public function configureDomainFilter(
    ?DomainFilterType $type = null,
    string $field = 'domainId',
    string $association = 'domains',
): self {
    $this->domainFilterType = $type;
    $this->domainFilterField = $field;
    $this->domainFilterAssociation = $association;

    return $this;
}

/**
 * @return $this
 */
public function setDomainFilterMode(DomainFilterMode $mode): self
{
    $this->domainFilterMode = $mode;

    return $this;
}
```
A do `getConfig()` ([:294](../../packages/administration/src/Component/Config/CrudConfig.php:294)) přidat předání nových hodnot do `CrudConfigData`.

#### 4. `CrudConfigData` – přenos + gettery
**File**: [`packages/administration/src/Component/Config/CrudConfigData.php`](../../packages/administration/src/Component/Config/CrudConfigData.php)
**Changes**: nové konstruktorové parametry a gettery.
```php
// konstruktor – přidat:
private bool $domainFilterDisabled,
private ?DomainFilterType $domainFilterType,
private string $domainFilterField,
private string $domainFilterAssociation,
private DomainFilterMode $domainFilterMode,

public function isDomainFilterDisabled(): bool
{
    return $this->domainFilterDisabled;
}

public function getDomainFilterType(): ?DomainFilterType
{
    return $this->domainFilterType;
}

public function getDomainFilterField(): string
{
    return $this->domainFilterField;
}

public function getDomainFilterAssociation(): string
{
    return $this->domainFilterAssociation;
}

public function getDomainFilterMode(): DomainFilterMode
{
    return $this->domainFilterMode;
}
```

### Success Criteria:

#### Automated Verification:
- [ ] Standardy projdou: `docker compose exec php-fpm php phing standards-fix`
- [ ] PHPStan projde: `docker compose exec php-fpm php phing phpstan`

#### Manual Verification:
- [ ] `CrudConfig` lze fluent-řetězit s ostatními metodami (vrací `self`).

---

## Phase 2: Servisa `CrudDomainFilterResolver`

### Overview
Servisa, která: (a) určí finální `DomainFilterType` (override/disable/auto), (b) resolvne vybrané `domainId` podle režimu z příslušné facade, (c) aplikuje filtr na `QueryBuilder`.

### Changes Required:

#### 1. Nová servisa
**File**: `packages/administration/src/Component/Crud/Domain/CrudDomainFilterResolver.php`
```php
<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Domain;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Config\DomainFilterMode;
use Shopsys\AdministrationBundle\Component\Config\DomainFilterType;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;

final class CrudDomainFilterResolver
{
    private const string DOMAIN_ID_FIELD = 'domainId';
    private const string FILTER_PARAMETER = 'crudDomainFilterId';

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        private readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
    ) {
    }

    /**
     * @param class-string $entityClass resolved entity class
     */
    public function resolveType(string $entityClass, CrudConfigData $config): DomainFilterType
    {
        if ($config->isDomainFilterDisabled()) {
            return DomainFilterType::NONE;
        }

        if ($config->getDomainFilterType() !== null) {
            return $config->getDomainFilterType();
        }

        return $this->autodetectType($entityClass, $config);
    }

    public function getSelectedDomainId(string $namespace, DomainFilterMode $mode): ?int
    {
        return match ($mode) {
            DomainFilterMode::SWITCH => $this->adminDomainTabsFacade->getSelectedDomainId(),
            DomainFilterMode::FILTER => $this->adminDomainFilterTabsFacade->getSelectedDomainId($namespace),
        };
    }

    public function applyFilter(
        QueryBuilder $queryBuilder,
        DomainFilterType $type,
        ?int $domainId,
        CrudConfigData $config,
    ): void {
        if ($type === DomainFilterType::NONE || $domainId === null) {
            return;
        }

        $rootAlias = ProxyQuery::DEFAULT_ALIAS;

        if ($type === DomainFilterType::SCALAR) {
            $queryBuilder
                ->andWhere(sprintf('%s.%s = :%s', $rootAlias, $config->getDomainFilterField(), self::FILTER_PARAMETER))
                ->setParameter(self::FILTER_PARAMETER, $domainId);

            return;
        }

        // COLLECTION → EXISTS subquery (no JOIN to avoid row multiplication / ProxyQuery alias collisions)
        $rootEntity = current($queryBuilder->getRootEntities());
        $manager = $this->managerRegistry->getManagerForClass($rootEntity);
        $metadata = $manager->getClassMetadata($rootEntity);
        $associationMapping = $metadata->getAssociationMapping($config->getDomainFilterAssociation());

        $subDql = sprintf(
            'SELECT 1 FROM %s crudDomainFilterDomain WHERE crudDomainFilterDomain.%s = %s AND crudDomainFilterDomain.%s = :%s',
            $associationMapping->targetEntity,
            $associationMapping->mappedBy,
            $rootAlias,
            self::DOMAIN_ID_FIELD,
            self::FILTER_PARAMETER,
        );

        $queryBuilder
            ->andWhere(sprintf('EXISTS (%s)', $subDql))
            ->setParameter(self::FILTER_PARAMETER, $domainId);
    }

    /**
     * @param class-string $entityClass
     */
    private function autodetectType(string $entityClass, CrudConfigData $config): DomainFilterType
    {
        $manager = $this->managerRegistry->getManagerForClass($entityClass);
        $metadata = $manager->getClassMetadata($entityClass);

        if ($metadata->hasField($config->getDomainFilterField())) {
            return DomainFilterType::SCALAR;
        }

        $association = $config->getDomainFilterAssociation();

        if ($metadata->hasAssociation($association)) {
            $targetMetadata = $manager->getClassMetadata($metadata->getAssociationTargetClass($association));

            if ($targetMetadata->hasField(self::DOMAIN_ID_FIELD)) {
                return DomainFilterType::COLLECTION;
            }
        }

        return DomainFilterType::NONE;
    }
}
```

> Pozn.: Servisa je autowired (autoconfigure v `administration` `services.yaml`); ověřit, že obě framework facades jsou jako služby dostupné (jsou – běžně injektované v controllerech).

### Success Criteria:

#### Automated Verification:
- [ ] Standardy: `docker compose exec php-fpm php phing standards-fix`
- [ ] PHPStan: `docker compose exec php-fpm php phing phpstan`
- [ ] Unit testy resolveru projdou (viz Phase 4): `docker compose exec php-fpm php phing tests-unit`

#### Manual Verification:
- [ ] EXISTS subquery vrací správné řádky pro `Category` při vybrané doméně (ověřit přes Adminer/SQL log).

---

## Phase 3: Napojení do `AbstractCrudController` + šablona

### Overview
`listAction` resolvne typ a vybranou doménu, aplikuje filtr před `configureQuery`, vystaví `selectedDomainFilterId` a předá flagy do šablony, která vykreslí příslušný partial nad grid.

### Changes Required:

#### 1. `AbstractCrudController`
**File**: [`packages/administration/src/Controller/AbstractCrudController.php`](../../packages/administration/src/Controller/AbstractCrudController.php)
**Changes**:
- nové `#[Required] public CrudDomainFilterResolver $crudDomainFilterResolver;`
- nové `protected ?int $selectedDomainFilterId = null;`
- úprava `listAction` ([:82](../../packages/administration/src/Controller/AbstractCrudController.php:82)):
```php
public function listAction(): Response
{
    $config = $this->definition->getConfig();
    $domainFilterType = $this->crudDomainFilterResolver->resolveType($this->definition->entityClass, $config);
    $domainFilterMode = $config->getDomainFilterMode();

    $this->selectedDomainFilterId = $domainFilterType === DomainFilterType::NONE
        ? null
        : $this->crudDomainFilterResolver->getSelectedDomainId($this->definition->controllerName, $domainFilterMode);

    $adapter = $this->ormAdapterFactory->create(
        $this->definition->entityClass,
        function (QueryBuilder $queryBuilder) use ($domainFilterType, $config): void {
            $this->crudDomainFilterResolver->applyFilter($queryBuilder, $domainFilterType, $this->selectedDomainFilterId, $config);
            $this->configureQuery($queryBuilder);
            $this->executeExtensions(fn (AbstractCrudControllerExtension $extension) => $extension->configureQuery($queryBuilder));
        },
    );

    $datagrid = $this->datagridFactory->create($adapter, [
        'crudDefinition' => $this->definition,
        'name' => $this->definition->entityName,
        'roleConstant' => $this->definition->getRoleConstant(),
    ]);
    $this->configureDatagrid($datagrid);
    $this->executeExtensions(fn (AbstractCrudControllerExtension $extension) => $extension->configureDatagrid($datagrid));

    return $this->render('@ShopsysAdministration/crud/list.html.twig', [
        'title' => $config->getTitle(ActionType::LIST),
        'grid' => $datagrid->createView(),
        'topActions' => $this->getConfiguredActions(ActionType::LIST),
        'hasDomainFilter' => $domainFilterType !== DomainFilterType::NONE,
        'isDomainSwitchMode' => $domainFilterMode === DomainFilterMode::SWITCH,
        'domainFilterNamespace' => $this->definition->controllerName,
    ]);
}
```
(přidat `use` pro `DomainFilterType`, `DomainFilterMode`).

> Pořadí v closure: doménový filtr **první**, pak `configureQuery`, pak extensions – aby vývojář mohl filtr dále zúžit, a aby `selectedDomainFilterId` bylo k dispozici v jeho `configureQuery`.

#### 2. Šablona list page
**File**: [`packages/administration/templates/crud/list.html.twig`](../../packages/administration/templates/crud/list.html.twig)
**Changes**: blok `main_content` vykreslí partial nad grid.
```twig
{% block main_content %}
    {% if hasDomainFilter %}
        {% if isDomainSwitchMode %}
            {{ render(controller('Shopsys\\FrameworkBundle\\Controller\\Admin\\DomainController::domainTabsAction')) }}
        {% else %}
            {{ render(controller('Shopsys\\FrameworkBundle\\Controller\\Admin\\DomainFilterController::domainFilterTabsAction', { namespace: domainFilterNamespace })) }}
        {% endif %}
    {% endif %}
    {{ grid.render }}
{% endblock %}
```

### Success Criteria:

#### Automated Verification:
- [ ] Standardy: `docker compose exec php-fpm php phing standards-fix`
- [ ] PHPStan: `docker compose exec php-fpm php phing phpstan`

#### Manual Verification:
- [ ] CRUD list nad SCALAR entitou (např. `Inquiry`) ukáže přepínač domén a grid je scoped na globálně vybranou doménu; změna domény v navbaru přefiltruje grid.
- [ ] CRUD list nad COLLECTION entitou (např. `Category`) funguje stejně.
- [ ] CRUD list nad ne-doménovou entitou nezobrazí žádný přepínač a grid je beze změny.
- [ ] Po `disableDomainFilter()` se přepínač nezobrazí a grid není filtrován.
- [ ] Po `setDomainFilterMode(FILTER)` se zobrazí „All domains“ filtr a `null` zobrazí vše.
- [ ] Embedded sub-request na `DomainController::domainTabsAction` projde access-controlem na CRUD stránce (ověřit pod běžným adminem).

---

## Phase 4: Testy

### Overview
Unit pokrytí resolveru (detekce + výběr facade + aplikace filtru) a doporučené funkční ověření filtrování.

### Changes Required:

#### 1. Unit test resolveru
**File**: `packages/administration/tests/Unit/Component/Crud/Domain/CrudDomainFilterResolverTest.php`
- `resolveType`: SCALAR (mock `ClassMetadata::hasField('domainId') === true`), COLLECTION (`hasAssociation('domains')` + target `hasField('domainId')`), NONE (nic), `isDomainFilterDisabled() === true` → NONE, explicitní `getDomainFilterType()` → ten typ (bez volání metadat).
- `getSelectedDomainId`: SWITCH → volá `AdminDomainTabsFacade::getSelectedDomainId()`, FILTER → `AdminDomainFilterTabsFacade::getSelectedDomainId($namespace)` (mock obou facades).
- `applyFilter`: SCALAR → ověřit `getDQL()` obsahuje `o.domainId = :crudDomainFilterId` a parametr je nastaven; COLLECTION → DQL obsahuje `EXISTS(...)` s `targetEntity`, `mappedBy` a `domainId`; `NONE`/`domainId === null` → DQL beze změny.

#### 2. (Doporučeno) Funkční test filtrování
**File**: `project-base/app/tests/App/Functional/Administration/CrudDomainFilterTest.php`
- Testovací CRUD controller nad SCALAR a COLLECTION entitou (fixture controller v test namespace), nastavit vybranou doménu přes facade, ověřit počet/identitu řádků v datasource pro doménu vs. „all“ (FILTER null).
- Pokud je zřízení test-only CRUD controlleru příliš nákladné, ponechat jako iterativní krok; primární pokrytí je unit.

### Success Criteria:

#### Automated Verification:
- [ ] Unit testy: `docker compose exec php-fpm php phing tests-unit`
- [ ] Funkční testy (pokud zřízeny): `docker compose exec php-fpm php phing tests-functional`
- [ ] Standardy + PHPStan: `docker compose exec php-fpm php phing standards-fix phpstan`

#### Manual Verification:
- [ ] Pokrytí zahrnuje všechny tři typy + oba režimy + disable + override.

---

## Phase 5: Dokumentace a upgrade notes

### Overview
Zdokumentovat auto-detekci a override API; upozornit na změnu chování.

### Changes Required:

#### 1. Dokumentace
**File**: `docs/administration/crud-controller/reference/domain-filter.md` (+ odkaz v `navigation.yml` a zmínka v [`configure-list-page.md`](../../docs/administration/crud-controller/getting-started/configure-list-page.md))
- Tabulka tří typů detekce, oba režimy (SWITCH default), override API (`disableDomainFilter`, `configureDomainFilter`, `setDomainFilterMode`), použití `protected ?int $selectedDomainFilterId` pro custom filtrování.

#### 2. Upgrade notes
**File**: dle [`/generate-upgrade-notes`](../../AGENTS.md) (spustit na konci)
- **Změna chování**: CRUD list page nad doménovou entitou nově automaticky zobrazí přepínač domén a scope-uje grid na vybranou doménu (default SWITCH). Opt-out přes `$config->disableDomainFilter()`.

### Success Criteria:

#### Automated Verification:
- [ ] Markdown/odkazy se renderují (kontrola v docs buildu, pokud existuje).

#### Manual Verification:
- [ ] Dokumentace pokrývá auto-detekci i všechny override scénáře.

---

## Testing Strategy

### Unit Tests:
- `CrudDomainFilterResolver`: detekce typu (mock `ClassMetadata`), výběr facade dle režimu (mock facades), generování DQL pro SCALAR i COLLECTION, no-op pro NONE/null.

### Manual Tests:
- SCALAR/COLLECTION/NONE entita; SWITCH vs FILTER; disable; override názvů sloupce/asociace; embedded partial access control.

### Manual Testing Steps:
1. Otevřít CRUD list nad `Inquiry` (SCALAR) → nahoře přepínač domén, grid scoped; přepnout doménu v navbaru → grid se přefiltruje.
2. Otevřít CRUD list nad `Category` (COLLECTION) → totéž; ověřit, že se neduplikují řádky.
3. Otevřít CRUD list nad ne-doménovou entitou → žádný přepínač, grid beze změny.
4. V controlleru zavolat `setDomainFilterMode(DomainFilterMode::FILTER)` → zobrazí se „All domains“; „All domains“ ukáže vše.
5. `disableDomainFilter()` → bez přepínače, bez filtru.

## Performance Considerations

- SCALAR: prostý `andWhere` na indexovaném `domainId` – zanedbatelné.
- COLLECTION: korelovaný `EXISTS` místo `JOIN` – vyhne se multiplikaci řádků a kolizi aliasů `ProxyQuery`; pro běžné objemy v adminu bez dopadu.

## Migration Notes

- Žádná DB migrace. Čistě aplikační/šablonová změna.
- Behaviorální dopad: aktuálně v repu nejsou žádné konkrétní CRUD controllery (`extends AbstractCrudController` = 0 výskytů), takže blast-radius v platformě je nulový; downstream projekty s CRUD controllery nad doménovými entitami uvidí auto-filtr – řeší upgrade notes a `disableDomainFilter()`.

## References

- Plán vychází z výzkumu v této session (CRUD vrstva `packages/administration`, obě domain facades).
- Klíčové soubory: [`AbstractCrudController`](../../packages/administration/src/Controller/AbstractCrudController.php), [`CrudConfig`](../../packages/administration/src/Component/Config/CrudConfig.php), [`ProxyQuery`](../../packages/administration/src/Component/Datagrid/Adapter/Orm/ProxyQuery.php), [`DomainController`](../../packages/framework/src/Controller/Admin/DomainController.php), [`DomainFilterController`](../../packages/framework/src/Controller/Admin/DomainFilterController.php).
