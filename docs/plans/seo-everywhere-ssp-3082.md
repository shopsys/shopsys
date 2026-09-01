# Jednotné a kompletní SEO u všech stránek (SSP-3082) — implementační plán

## Overview

SEO specialisté chtějí u **každé stránky s vlastní/friendly URL** jednotně nastavovat *page title*, *meta description*, *meta robots* a *canonical URL* (multidoménově, nepovinně) a mít jistotu, že storefront tyto hodnoty vyrenderuje už v SSR, že sitemapa respektuje `noindex`/canonical a že homepage i statické stránky se řídí stejným mechanismem (SEO pages) jako entity. Součástí je odstranění duplicitního nastavení „Headline / Meta description“ ze *Settings > SEO > SEO* a doseedování SEO pages pro všechny nedynamické stránky storefrontu.

Architektonický záměr navíc: SEO atributy **přestat opakovat** — jedna Doctrine `Embeddable` hodnota `SeoAttributes`, jeden `SeoAttributesData`, jeden admin `SeoAttributesFormType`, jeden GraphQL typ `SeoAttributes` a jeden storefront fragment. Refaktor existujících entit se dělá **nejdřív** (behaviour-preserving, ověřitelný stávajícími testy), nové entity a nová pole vznikají rovnou ve finální podobě.

Jira: [SSP-3082](https://shopsys.atlassian.net/browse/SSP-3082) (epic SSP-183 SEO). Branch `rv/seo-seo-everywhere`, cíl `20.0`. **Jeden PR**, fáze = atomické commity dle `commit-conventions`.

## Current State Analysis

### Backend — SEO pole per entita (stav před změnou)

| Entita | title / metaDesc / H1 | canonical | robots | umístění |
|---|---|---|---|---|
| Product | ✅ | ❌ | ❌ | `ProductDomain.php:49,56,77`; přes ES (`ProductExportFieldProvider.php:42-44`, `ProductArrayFieldMapper.php:170-183`) |
| Category | ✅ | ❌ | ❌ | `CategoryDomain.php:46-60`; `CategoryResolverMap.php:66-91`; DQL `CategoryRepository.php:342` |
| Article | ✅ (single-domain, přímo na entitě) | ❌ | ❌ | `Article.php:83-97`; ES `ArticleExportRepository.php:70-72` |
| BlogArticle | ✅ | ❌ | ❌ | `BlogArticleDomain.php:39-53`; ES `BlogArticleExportRepository.php:98-100` |
| BlogCategory | ✅ | ❌ | ❌ | `BlogCategoryDomain.php:38-52`; `BlogCategoryResolverMap.php:34-42` |
| Brand | ✅ | ❌ | ❌ | `BrandDomain.php:46-60`; `BrandResolverMap.php:37-45`; DQL `BrandRepository.php:110` |
| **Store** | ❌ | ❌ | ❌ | `Store.php` — single-domain (`domainId`), `StoreFormType.php` bez SEO skupiny |
| **Flag** | ❌ | ❌ | ❌ | translatable bez domain vrstvy; `FlagFormType.php:59-70` má skupinu „Seo“ jen s `urls` |
| ReadyCategorySeoMix | ✅ jako `title`/`metaDescription`/`h1` (h1 NOT NULL) | ❌ | ❌ | `ReadyCategorySeoMix.php:80-108`; fallbacky v `CategoryResolverMap.php:78-91` |
| SeoPage | title/metaDesc ✅, bez H1 | ✅ `canonicalUrl` (sloupec `canonical_url`) | ❌ | `SeoPageDomain.php:39-74`, og pole, `SeoPageFormType.php:113-163` |

- Žádná z 10 tříd nemá rodiče (Flag řeší nová `FlagDomain`) — dědičnost by šla, ale je to mixin přes inheritance; **`ORM\Embeddable` v repu zatím není** (grep prázdný), phpstan pravidlo `packages/mcp/src/Phpstan/McpEntityExposureAttributeRule.php:212` už ale `ORM\Embedded` očekává (vyžaduje `#[AsMcpColumn]`). `MappedSuperclass` precedens: `AbstractNumberSequence`, `AbstractUploadedFile`; `EntityExtensionListener.php:106-118` s ním počítá.
- Sloupce všech existujících SEO polí jsou `seo_title`, `seo_meta_description`, `seo_h1` → embeddable s `columnPrefix: 'seo_'` se mapuje 1:1. Výjimky: SeoPage `canonical_url`, ReadyCategorySeoMix `title`/`meta_description`/`h1` (NOT NULL).
- Meta robots jako per-entity pojem neexistuje; jediný „robots“ je globální robots.txt.
- Neexistuje sdílený SEO form type — 10 formulářů duplikuje `MultidomainType` pole; hinty `data-js-recommended-length` 60/155 (`packages/administration/assets/src/js/utils/recommendedLength.js`). `project-base/app/src/Form/Admin/ProductFormTypeExtension.php:37` odebírá `seoH1s`; `project-base/app/src/Model/Product/Product.php:82` čte `array_keys($productData->seoTitles)`.
- `TransformStringHelper::convertHtmlToPlainText()` (`packages/framework/src/Component/String/TransformStringHelper.php:112-124`) existuje; truncate helper ne.
- `Constraints\Url` jen u SeoPage canonical (`SeoPageFormType.php:140`), `protocols: ['https']` nikde.

### Frontend API
GraphQL pole `seoTitle`/`seoMetaDescription`/`seoH1` jsou plochá na každém typu (`ProductDecorator.types.yaml:96-102`, `CategoryDecorator:64-70`, `BrandDecorator:24-30`, `BlogCategoryDecorator:25-31`, `BlogArticleDecorator`, `ArticleInterfaceDecorator:15-20`, `ArticleDecorator:18-24`); SeoPage má `title`/`metaDescription`/`canonicalUrl`/`ogTitle`/`ogDescription`/`ogImage`/`hreflangLinks`. Interface vzor: `ModelType/Hreflang/HreflangDecorator.types.yaml` (`type: interface, decorator: true`) + project-base `Hreflang.types.yaml` (inherits). Product resolvuje přes `ProductResolverMap::getObjectMethodForField()` → mapper metoda `get<Field>()`.

### Storefront
- Jediná hlavička `components/Basic/Head/SeoMeta.tsx` + hook `utils/seo/useSeo.ts` (SeoPage → props stránky → `settings.seo`). Suffix `titleAddOn` se lepí vždy (trailing space, když je prázdný).
- Robots: komponenta `MetaRobots.tsx` na 24 místech (cart, search `noindex, nofollow`, order/*, customer/*, personal-data-*, new-password, order-detail, order-withdrawal*; category/brand/flag `noindex, follow` při `filter || sort`; blog draft/preview/future `noindex, nofollow`).
- Canonical vždy (`SeoMeta.tsx:76`); `generateCanonicalUrl.ts` nechává `page`, `q`, `filter`, `sort`. Hreflang + `x-default` vždy, když existují.
- Store title = `t('Store') + name` (`pages/stores/[storeSlug].tsx:34`), Brand title = `"Značka " + seoTitle` (`pages/brands/[brandSlug].tsx:60`). Flag bez seo polí.
- Bez `settings.seo.title` ztrácí titulek: `HomePageContent.tsx:30`, `pages/contact-form.tsx:14`, `pages/personal-data-overview/[hash].tsx:37`, `pages/customer/order-detail.tsx:56`.

### Sitemapa
`packages/framework/src/Model/Sitemap/SitemapRepository.php` — 7 DQL metod, výsledek jen `slug` + `entityId`. Bez funkčního testu (vzor `project-base/app/tests/App/Functional/Model/ImageSitemap/ImageSitemapTest.php`).

### Settings > SEO
`seoTitleMainPage`/`seoMetaDescriptionMainPage`: `SeoSettingFormType.php:48-82`, `SeoController.php:35-47`, `SeoSettingFacade.php:14-72`, `RequiredSettingExtension.php:94,329-353`, Twig `SeoExtension.php:29,45-50` (nevolá žádná šablona), FE API `SeoSettingDecorator.types.yaml:11-19` + `SeoSettingsQuery.php:26,28`, placeholder v 6 formulářích (`CategoryFormType:72`, `ProductFormType:604`, `BrandFormType:56`, `BlogCategoryFormType:150`, `BlogArticleFormType:317`, `ArticleFormType:224-242`), `SettingValueDataFixture.php:109-118`, test `tests/FrontendApiBundle/Functional/Settings/SeoSettingTest.php`, docs `docs/model/domain-limiting.md:94`, storefront `useSeo.ts:35-36`, `SeoSettingFragment.graphql:3,5`.

### SEO pages v čisté instalaci
`Version20240108154625.php:16-51` seeduje 9 stránek (cs/en, ostatní locale = cs) + `Version20250119100000.php` Catalog. Slugy nesedí se storefront `config/routes.ts` (`kontakt`/`contact` vs `/kontaktni-formular`/`/contact-form`, `forgot-password` vs `/reset-password`, `cookie-consent` vs `/user-consent`, SK doména má cs slugy). Chybí wishlist, product-comparison, search, new-password, personal-data-*, order/*, order-confirmation, customer/*. `front_page_seo` friendly URL používá jen `FriendlyUrlRepository.php:267` (mapování entity) — storefront je nepotřebuje.

### Souvislosti a rizika
- SSP-948 (canonical) Done, ale skončil u SeoPage. Otevřený PR #4810 (SSP-4275) mění `FriendlyUrlFacade` + `Product/Category/Brand/ArticleFacade` — rebase po merge.
- Produkty i články jdou přes Elasticsearch → nová pole = úprava 9 definic + reindex (upgrade note).

## Desired End State

1. Jedna hodnota `SeoAttributes` (title, metaDescription, h1, metaRobots, canonicalUrl) embedovaná ve všech 10 entitách; admin ji edituje jediným `SeoAttributesFormType` (multidoménově přes `MultidomainType`) — **všech 5 polí u všech entit včetně H1 u SeoPage**, hinty 60/160, robots select (nevyplněno / `noindex` / `nofollow` / `noindex, nofollow`), canonical https-only s JS varováním při cross-domain.
2. FE API: každý typ implementuje interface `Seo` a vrací `seo: SeoAttributes!` (metaDescription **s fallbackem** z popisu/perexu: bez HTML, whitespace, ≤160 znaků, celé slovo). Plochá pole `seoTitle`/`seoMetaDescription`/`seoH1` a SeoPage `title`/`metaDescription`/`canonicalUrl` jsou odstraněna.
3. Storefront v SSR: title = admin ?? název entity (Store „Prodejna …“, Brand prefix zachován) + suffix; description tag jen pokud existuje; description → og/twitter; robots = SeoPage/entita ?? hardcoded default; canonical = SeoPage/entita ?? self; při `noindex` bez canonical i hreflang.
4. Sitemapa vynechá entity s canonical nebo `noindex`.
5. *Settings > SEO > SEO* jen *Complement to title*; hodnoty přeneseny do SeoPage `/`.
6. Čistá instalace má SeoPage pro všechny uživatelské statické stránky (en/cs/sk), u neindexovatelných `noindex`.

### Key Discoveries
- Embeddable sloupce: `#[ORM\Embedded(class: SeoAttributes::class, columnPrefix: 'seo_')] protected $seo;` → `seo_title`, `seo_meta_description`, `seo_h1`, `seo_meta_robots`, `seo_canonical_url` — existující data bez migrace (kromě SeoPage `canonical_url` a SeoMix přejmenování).
- Doctrine embeddable je vždy instancován (i když jsou všechny sloupce NULL) → gettery nikdy nevrací `null` objekt.
- BC plocha přejmenování `seoTitles[...]` → `seo[$domainId]`: 6 forem, 6 DataFactory, fixtures `BlogArticle/Brand/Category/Product/SeoPage DataFixture`, testy `Category/Brand/ProductDomainTest`, `SeoPage/SeoPageTest`, `project-base/app/src/Model/Product/Product.php:82`, `ProductFormTypeExtension.php:37`.
- Multidomain vzor Flag: `BrandDomain.php` + `Brand::createDomains()/setDomains()` (`Brand.php:128-148`).
- `GroupType` (`packages/framework/src/Form/GroupType.php`) je `final`, `inherit_data: true`; `MultidomainType` podporuje `entry_type` compound typ a `options_by_domain_id`.
- Enum konvence `AbstractEnum` + `getAllIndexedByTranslations()`; ChoiceType + `Constraints\Choice` (`NavigationItemFormType.php:60-80`).
- ES definice `project-base/app/src/Resources/definition/{product,blog_article,article}/{1,2,3}.json` per doména, in-place (precedent `7b980a3ed3`); `ProductExportRepositoryTest.php:95-160` hlídá seznam polí.
- Migrace v `packages/framework/src/Migrations/` (`final class VersionX extends Shopsys\MigrationBundle\...\AbstractMigration`, domény přes `DomainAwareInterface` + `MultidomainMigrationTrait`); mazání settingu `Version20240426045700.php:16-18`.
- Překlady `phing translations-dump` (en prázdný `msgstr`), storefront `pnpm run translate`; schema `make generate-schema` + `make check-schema`.
- Testy: `GraphQlTestCase`, `TransactionFunctionalTestCase`, unit `packages/framework/tests/Unit/...`, vitest `project-base/storefront/vitest/utils/*.test.ts`.
- phpstan: entity/Data properties bez typehintů (`OrmPropertyHasNoTypehintRule`, `EntityDataObjectPropertyHasNoTypehintRule`); ECS vyžaduje `#[Override]`.

## What We're NOT Doing
- Og pole u entit mimo SeoPage (SeoPage og pole zůstávají mimo `SeoAttributes`).
- Rozšíření sitemapy o brandy, prodejny, blog kategorie, SeoPages; hreflang pro Store/Article.
- Změna hardcoded robots defaultů storefrontu ani robots.txt; filtrování hreflang podle `noindex` cílové stránky.
- Tvrdé délkové validace title/description (jen JS hint).
- Změna prefixu „Značka“ u brandu; přesun `SeoExtension::getSeoTitleAddOn`.
- Deprecation období pro plochá GraphQL pole (odstraňují se rovnou, 20.0 BC breaky nese).

## Implementation Approach

Package-first. Pořadí: **spike → refaktor existujícího SEO do `SeoAttributes` (embeddable + Data + Form + GraphQL + fragment) → nová pole v embeddable → Store/Flag → sitemapa → settings → SEO pages → storefront chování → notes.** Fallback description v backendu (`SeoAttributesResultFactory`), robots nullable string validovaný enumem (v GraphQL `String`, hodnota `noindex, nofollow` není validní enum case).

### Dělba zodpovědností BE × SF (rozhodnuto, neměnit během implementace)

| zodpovědnost | vrstva | poznámka |
|---|---|---|
| `seo.metaDescription` fallback z popisu/perexu/textu (strip HTML, ≤160, celá slova) | **BE** (`SeoAttributesResultFactory`) | jediná počítaná hodnota na BE; SF ji jen renderuje (a přebírá do og/twitter) |
| SEO mix `title ?? h1`, `metaDescription ?? category` | **BE** | dnešní chování `CategoryResolverMap.php:85-87`, jen zachováno |
| `seo.title`, `seo.h1`, `seo.metaRobots`, `seo.canonicalUrl` | **BE** vrací surové admin hodnoty (nullable) | žádné dopočty |
| title fallback na název entity, prefixy „Prodejna“/„Značka“, **stránkovací dovětky** (`useSeoTitleWithPagination`), suffix `titleAddOn`, h1 fallback | **SF** | beze změny logiky, plán do `useSeoTitleWithPagination` nesahá |
| robots default per typ stránky (filter/sort, draft blog, cart…), merge admin > default | **SF** (`resolveMetaRobots`) | hardcoded defaulty jsou znalost SF |
| self-canonical z aktuální URL, potlačení canonical/hreflang při noindex | **SF** | potřebuje router/kontext requestu |

Důsledek pro `SeoMeta`: prop `seo` (entita) je zdrojem **pouze** pro `metaRobots` a `canonicalUrl`; `title`/`description` do `SeoMeta` dál tečou přes `defaultTitle`/`defaultDescription`, které si stránka spočítá sama (název entity, prefix, stránkování). `seo.title`/`seo.h1` čte stránka, ne `SeoMeta`.

### Cílový tvar (rozhodnuto)

| vrstva | tvar |
|---|---|
| Entity | `Shopsys\FrameworkBundle\Model\Seo\SeoAttributes` (`#[ORM\Embeddable]`): `title`, `metaDescription`, `h1`, `metaRobots`, `canonicalUrl`; v `*Domain`/single-domain entitě `#[ORM\Embedded(columnPrefix: 'seo_')] protected $seo` |
| Hlavní entita | `getSeoAttributes(int $domainId): SeoAttributes` (single-domain bez parametru); dosavadní `getSeoTitle($domainId)` atd. zůstávají jako delegáty (ES export, feedy) |
| Data | `SeoAttributesData` (5 public properties); multidomain `*Data::$seo = []` (`@var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData[]` indexed by domainId), single-domain `*Data::$seo` (objekt) |
| DataFactory | `SeoAttributesDataFactory::create()`, `createFromSeoAttributes()` |
| Admin | `SeoAttributesFormType` (per doména, `data_class: SeoAttributesData`) + `SeoGroupType` (parent `GroupType`, skládá `seo` přes `MultidomainType(entry_type: SeoAttributesFormType)` nebo přímo u single-domain **a** `urls` přes `UrlListType` dle `url_list_options`); formulář přidá jediné pole `seoGroup` |
| GraphQL | interface `Seo { seo: SeoAttributes! }`, type `SeoAttributes { title, metaDescription, h1, metaRobots, canonicalUrl }`; resolvery vrací DTO `SeoAttributesResult` z `SeoAttributesResultFactory` (fallback description) |
| DB | `seo_title TEXT`, `seo_meta_description TEXT`, `seo_h1 TEXT`, `seo_meta_robots VARCHAR(30)`, `seo_canonical_url TEXT` — vše NULL |
| Storefront | `SeoAttributesFragment`, `CommonLayout`/`SeoMeta` prop `seo` |

---

## Phase 0: Spike — Doctrine embeddable v Shopsys (time-box 2 h, rozhodovací brána)

### Overview
Ověřit, že `ORM\Embeddable` funguje se Shopsys entity extension, MCP atributy a kontrolami; teprve pak se na něj vsadit.

### Changes Required
1. Vytvořit `packages/framework/src/Model/Seo/SeoAttributes.php` (embeddable, 5 polí, `#[AsMcpColumn]` na properties — ověřit, co vyžaduje `McpEntityExposureAttributeRule`), na `BrandDomain` nahradit tři sloupce `#[ORM\Embedded(class: SeoAttributes::class, columnPrefix: 'seo_')] #[AsMcpColumn] protected $seo;`, přepsat gettery na delegáty.
2. Spustit: `docker compose exec php-fpm php phing clean db-check-mapping` (žádný diff → sloupce sedí 1:1), `phing annotations-check phpstan` (MCP pravidlo, ORM typehint pravidla na embeddable properties), `phing tests-unit`, `project-base/app/tests/App/Functional/Model/Product/Brand/BrandDomainTest.php`, admin edit brandu ručně, `BrandRepository.php:110` DQL na `bd.seo.title`.
3. Ověřit project-base extension: vytvořit dočasně `App\Model\Product\Brand\BrandDomain extends BaseBrandDomain` a zkontrolovat `EntityExtensionListener` (mapping embeddable na potomka) přes `db-check-mapping`.

### Success Criteria
#### Automated Verification
- [ ] `docker compose exec php-fpm php phing clean db-check-mapping annotations-check phpstan tests-unit` prochází s embeddable na `BrandDomain`
- [ ] `BrandDomainTest` prochází
#### Manual Verification
- [ ] Editace brandu v adminu uloží a načte SEO hodnoty
- [ ] **Brána:** pokud cokoli výše nelze rozumně opravit do 2 h → fallback: `#[ORM\MappedSuperclass] AbstractSeoDomain` pro 7 `*Domain` tříd, plochá pole u Store/Article/SeoMix, zbytek plánu (Data/Form/GraphQL sjednocení) platí beze změny.

---

## Phase 1: Refaktor existujících SEO atributů na `SeoAttributes` (behaviour-preserving)

### Overview
Šest entit + SeoPage + ReadyCategorySeoMix přejdou na embeddable, Data objekty na `seo`, formuláře na `SeoAttributesFormType`, GraphQL na `seo: SeoAttributes!`, storefront na jeden fragment. Chování storefrontu i adminu se nemění (kromě hintu 155→160).

### Changes Required

#### 1. Model
- `packages/framework/src/Model/Seo/SeoAttributes.php` — embeddable: properties bez typehintů s `@var string|null`, `#[ORM\Column(type: 'text', nullable: true)]` (`title`, `metaDescription`, `h1`, `canonicalUrl`), `#[ORM\Column(type: 'string', length: 30, nullable: true)]` (`metaRobots`); `edit(SeoAttributesData $data): void` + gettery. (Pole `metaRobots`/`canonicalUrl` jsou v embeddable od začátku, aby Phase 2 jen přidala sloupce.)
- `packages/framework/src/Model/Seo/SeoAttributesData.php`, `SeoAttributesDataFactory.php` (`create()`, `createFromSeoAttributes(SeoAttributes)`).
- `ProductDomain.php`, `CategoryDomain.php`, `BlogArticleDomain.php`, `BlogCategoryDomain.php`, `BrandDomain.php`, `SeoPageDomain.php`, `Article.php`, `ReadyCategorySeoMix.php` — nahradit SEO properties `$seo` embeddable; v konstruktoru `$this->seo = new SeoAttributes()`; gettery `getSeoAttributes()`, delegáty `getSeoTitle()` … zachovat.
- Hlavní entity (`Product.php:770-772,879`, `Category.php:333-343`, `BlogArticle.php:316-325`, `BlogCategory.php:307-316`, `Brand.php:128-148`, `SeoPage.php:148-172`) — `setDomains()` volá `$domain->getSeoAttributes()->edit($data->seo[$domainId])`; `createDomains()` bere domain ids z `array_keys($data->seo)`; `getSeoAttributes(int $domainId)`.
- `ReadyCategorySeoMix`: `h1` zůstává povinný ve formuláři (`NotBlank`), sloupec se stane nullable v rámci embeddable; `getH1()`/`getTitle()`/`getMetaDescription()` delegáty.

#### 2. Data + DataFactory
- `ProductData.php:114-154,261-269`, `CategoryData.php:19-29,84-86`, `BlogArticleData.php:25-35,101-103`, `BlogCategoryData.php:19-29,64-66`, `BrandData.php:34-44,56-58`, `SeoPageData.php` (`seoTitlesIndexedByDomainId`, `seoMetaDescriptionsIndexedByDomainId`, `canonicalUrlsIndexedByDomainId` → `seo`), `ArticleData.php:25-55`, `ReadyCategorySeoMixData.php:42-62` (`h1`, `title`, `metaDescription` → `seo`).
- DataFactory (`ProductDataFactory.php:82-84,124-126`, `CategoryDataFactory.php:49-51,67-69`, `BlogArticleDataFactory.php:43-45,76-78`, `BlogCategoryDataFactory.php:39-41,63-65`, `BrandDataFactory.php:38-40,69-71`, `SeoPageDataFactory.php:18-51`, `ArticleDataFactory.php:48-53`, `ReadyCategorySeoMixDataFactory.php`) — inject `SeoAttributesDataFactory`, `$data->seo[$domainId] = $this->seoAttributesDataFactory->create()` / `createFromSeoAttributes($entity->getSeoAttributes($domainId))`.
- project-base: `project-base/app/src/Model/Product/Product.php:82` → `array_keys($productData->seo)`.

#### 3. Admin formulář
- `packages/framework/src/Form/Admin/Seo/SeoAttributesFormType.php` (`final`, `data_class: SeoAttributesData`):
```php
final class SeoAttributesFormType extends AbstractType
{
    public function __construct(private readonly SeoMetaRobotsEnum $seoMetaRobotsEnum) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->getFieldConfigurations() as $fieldName => [$fieldType, $defaultFieldOptions]) {
            $builder->add($fieldName, $fieldType, array_replace_recursive(
                $defaultFieldOptions,
                $options['options_by_field_name'][$fieldName] ?? [],
            ));
        }
    }

    /**
     * @return array<string, array{class-string<\Symfony\Component\Form\AbstractType>, array<string, mixed>}>
     */
    private function getFieldConfigurations(): array
    {
        return [
            'title' => [TextType::class, [
                'required' => false,
                'label' => 'Page title',
                'attr' => ['data-js-recommended-length' => 60],
            ]],
            'metaDescription' => [TextareaType::class, [
                'required' => false,
                'label' => 'Meta description',
                'attr' => ['data-js-recommended-length' => 160],
            ]],
            'h1' => [TextType::class, [
                'required' => false,
                'label' => 'Heading (H1)',
            ]],
            // metaRobots (ChoiceType z SeoMetaRobotsEnum) + canonicalUrl (UrlType, Url(protocols: ['https'])) doplní Phase 2
        ];
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeoAttributesData::class,
            'label' => false,
            'options_by_field_name' => [],
        ]);
        $resolver->setAllowedTypes('options_by_field_name', 'array');
        // normalizer: klíče musí být podmnožinou názvů polí z getFieldConfigurations() — fail fast na překlep
    }
}
```
Sémantika merge: `array_replace_recursive` — skaláry (`required`, `label`) se nahrazují, vnořená pole (`attr`) doplňují; `constraints` se **nahrazují celé** (volající dodá kompletní seznam) — predikovatelnější než `array_merge_recursive`, které by skaláry slévalo do polí. Pozor: `MultidomainType.php:39` merguje `entry_options` s `options_by_domain_id` přes `array_merge_recursive` — per-doménová vrstva proto smí nastavovat jen klíče, které sdílená `entry_options` nenastavuje (u nás: per-doménové `attr` hodnoty, žádný konflikt).
- `packages/framework/src/Form/Admin/Seo/SeoGroupType.php` (`final`, `getParent(): GroupType::class`) — skupina „SEO“ skládající per-doménové atributy **a** `urls`. `UrlListType` je jedno pole pro všechny domény mapované na `*Data::$urls` (`UrlListData`), proto nemůže být uvnitř per-doménového `SeoAttributesFormType`; dnes leží v SEO skupině u 7 z 10 formulářů (Product `:635`, Category `:188`, Brand `:122`, BlogArticle `:129`, BlogCategory `:199`, Article `:177`, Flag `:64`), Store ho má v `basicInformationGroup:92`, SeoMix na top-levelu `:30`, SeoPage ho nemá:
```php
final class SeoGroupType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['multidomain']) {
            $builder->add('seo', MultidomainType::class, [
                'entry_type' => SeoAttributesFormType::class,
                'entry_options' => $options['seo_attributes_options'], // typicky ['options_by_field_name' => ...]
                'options_by_domain_id' => $options['seo_attributes_options_by_domain_id'], // per-doménové options_by_field_name (attr: placeholder source id, canonical domain url)
                'required' => false,
                'label' => false,
            ]);
        } else {
            $builder->add('seo', SeoAttributesFormType::class, $options['seo_attributes_options']);
        }

        if ($options['url_list_options'] !== null) {
            $builder->add('urls', UrlListType::class, ['label' => 'URL addresses'] + $options['url_list_options']);
        }
    }

    #[Override]
    public function getParent(): string
    {
        return GroupType::class;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => t('SEO'),
            'multidomain' => true,
            'seo_attributes_options' => [],
            'seo_attributes_options_by_domain_id' => [],
            'url_list_options' => null, // route_name, entity_id, limit_domains_by_ids, required, constraints; null = bez pole urls
        ]);
        $resolver->setAllowedTypes('multidomain', 'bool');
        $resolver->setAllowedTypes('url_list_options', ['array', 'null']);
    }
}
```
- Použití ve formuláři (jediný řádek místo dnešní skupiny):
```php
$builder->add('seoGroup', SeoGroupType::class, [
    'seo_attributes_options_by_domain_id' => $optionsByDomainId,
    'url_list_options' => $product !== null ? ['route_name' => 'front_product_detail', 'entity_id' => $product->getId()] : null,
]);
```
- **H1 je ve skupině vždy** — i u SeoPage (sdílený embeddable stejně vynucuje sloupec `seo_h1` a GraphQL `seo.h1`, skrývat jen formulářové pole by vytvořilo mrtvý atribut). Žádná `include_h1` option neexistuje — jednotnost záměrně nemá per-entity vypínače; pokud si projekt chce SEO skupinu upravit, udělá si extension pro `SeoAttributesFormType`.
- Přepsat: `ProductFormType.php:586-640`, `CategoryFormType.php:56-82,160-190` (`entity_id` může být `null`, urls vždy), `BrandFormType.php:40-66,102-122`, `BlogArticleFormType.php:99-129,301-329`, `BlogCategoryFormType.php:134-199`, `FlagFormType.php:59-70` (v Phase 3), `SeoPageFormType.php:113-163` (h1 nově i zde; `url_list_options: null`; og pole a image přidat do téhož builderu skupiny), `ArticleFormType.php:146-177,224-242` (`multidomain: false`, `seo_attributes_options: ['options_by_field_name' => ['canonicalUrl' => ['attr' => ['data-js-canonical-domain-url' => …]], 'title' => ['attr' => ['data-js-placeholder-source-input-id' => …]]]]`, `url_list_options` s `limit_domains_by_ids`), `ReadyCategorySeoCombinationFormType.php:30-66` (`multidomain: false`, `seo_attributes_options: ['options_by_field_name' => ['h1' => ['required' => true, 'constraints' => [new NotBlank()]]]]`, `url_list_options: ['required' => true, 'constraints' => [new NotBlank()], …]` — urls se přesunou z top-levelu do skupiny), `StoreFormType.php` (v Phase 3: `multidomain: false`, urls přesun z `basicInformationGroup`).
- Sjednocení labelů: skupina „SEO“, urls „URL addresses“ (dnes mix „URL settings“/„URL addresses“, „SEO“/„Seo“) — upgrade note nezmiňovat, jde o překladové klíče (`translations-dump`).
- Odstranit placeholder `getDescriptionMainPage()` a nevyužité injekce `SeoSettingFacade` z těchto form typů (ProductFormType, CategoryFormType, BrandFormType, BlogArticleFormType, BlogCategoryFormType, ArticleFormType).
- `project-base/app/src/Form/Admin/ProductFormTypeExtension.php` — **smazat `setSeoGroup()` / `remove('seoH1s')`** (pozůstatek: pole bylo skryté ve formuláři, ale aplikace s `seoH1` dál pracuje — ES export, API, storefront); extension zůstane jen s `formBuilderHelper->disableFieldsByConfigurations()`. Produktový formulář v demo projektu tím nově zobrazí H1 — žádoucí sjednocení.

#### 4. Migrace (přejmenování, kde sloupce nesedí)
`packages/framework/src/Migrations/Version<ts>.php`:
```php
$this->sql('ALTER TABLE seo_page_domains RENAME COLUMN canonical_url TO seo_canonical_url');
$this->sql('ALTER TABLE ready_category_seo_mixes RENAME COLUMN title TO seo_title');
$this->sql('ALTER TABLE ready_category_seo_mixes RENAME COLUMN meta_description TO seo_meta_description');
$this->sql('ALTER TABLE ready_category_seo_mixes RENAME COLUMN h1 TO seo_h1');
$this->sql('ALTER TABLE ready_category_seo_mixes ALTER seo_h1 DROP NOT NULL');
```
Přidání `seo_meta_robots`/`seo_canonical_url` do všech tabulek dělá Phase 2. Aby Phase 1 zůstala čistý refaktor a `db-check-mapping` seděl, mapuje embeddable v Phase 1 jen 3 pole (title, metaDescription, h1 — SeoPage má navíc `canonicalUrl` → řešit `SeoPageDomain` mapováním s 4 poli není možné u sdíleného embeddable; proto **rozhodnutí při implementaci**: buď (a) embeddable rovnou s 5 poli a migrace Phase 1 přidá i `seo_meta_robots`/`seo_canonical_url` sloupce (Phase 2 pak přidává jen chování), nebo (b) SeoPage canonical dočasně mimo embeddable. Preferováno (a) — sloupce bez chování jsou neškodné, commit zůstává „refactor + schema“.)

#### 5. DQL
`BrandRepository.php:110` → `bd.seo.h1`, `bd.seo.title`, `bd.seo.metaDescription`; `CategoryRepository.php:342` → `cd.seo.*`.

#### 6. FE API — typ `SeoAttributes` + interface `Seo`
- `packages/frontend-api/src/Resources/config/graphql-types/ModelType/Seo/SeoAttributesDecorator.types.yaml` (object: `title`, `metaDescription`, `h1`, `metaRobots`, `canonicalUrl` — vše `String`), `ModelType/Seo/SeoDecorator.types.yaml` (interface `seo: SeoAttributes!`); project-base `app/config/graphql/types/ModelType/Seo/SeoAttributes.types.yaml` + `Seo.types.yaml` (inherits).
- Dekorátory: přidat `interfaces: ['Seo']` + `seo: SeoAttributes!` a **smazat** plochá pole: `ProductDecorator:96-102`, `CategoryDecorator:64-70`, `BrandDecorator:24-30`, `BlogCategoryDecorator:25-31`, `BlogArticleDecorator`, `ArticleInterfaceDecorator:15-20` + `ArticleDecorator:18-24`, `SeoPageDecorator:9-15` (ponechat `ogTitle`, `ogDescription`, `ogImage`, `hreflangLinks`).
- DTO `packages/frontend-api/src/Model/Seo/SeoAttributesResult.php` (readonly, 5 polí) a `SeoAttributesResultFactory.php`:
  - `createFromSeoAttributes(SeoAttributes $seo, ?string $fallbackDescriptionHtml = null): SeoAttributesResult`
  - `createFromArray(array $data, array $keys, ?string $fallbackDescriptionHtml = null): SeoAttributesResult` (ES: produkt `seo_title`…, články `seoTitle`…)
  - v Phase 1 bez fallbacku (jen kopie), fallback logiku doplní Phase 2.
- Resolvery: `CategoryResolverMap.php` (`'seo' => factory->createFromSeoAttributes($category->getSeoAttributes($d))`; SEO mix: `title ?? h1`, `metaDescription ?? category metaDescription` zachovat přes `createForReadyCategorySeoMix($mix, $category, $domainId)`), `BrandResolverMap`, `BlogCategoryResolverMap`, `SeoPageResolverMap` (`'seo'`), `BlogArticleResolverMap` (`'seo' => createFromArray`), `ArticleResolverMap` (`'seo'` pro `ArticleSite`), `ProductArrayFieldMapper::getSeo(array $data)`, `ProductEntityFieldMapper::getSeo(Product $product)` (smazat `getSeoH1/getSeoTitle/getSeoMetaDescription`).
- `make generate-schema`.

#### 7. Storefront (jen přepnutí na `seo`)
- `graphql/requests/seo/fragments/SeoAttributesFragment.graphql` (`title metaDescription h1 metaRobots canonicalUrl`).
- Detail fragmenty nahradí `seoTitle seoMetaDescription seoH1` → `seo { ...SeoAttributesFragment }`: `ProductDetailInterfaceFragment.graphql:51-55`, `CategoryDetailFragment.graphql:13-23`, `BrandDetailFragment.graphql:10-15`, `BlogCategoryDetailFragment.graphql:11-16`, `BlogArticleDetailFragment.graphql:17-22`, `ArticleDetailFragment.graphql:11-14`, `SeoPageFragment.graphql` (`title metaDescription canonicalUrl` → `seo {…}`).
- Stránky/komponenty: `pages/products/[productSlug].tsx:62-67` (`product.seo.title || product.name`, `description={product.seo.metaDescription}`), `categories/[categorySlug].tsx:49-72` + `CategoryDetailContent.tsx:39`, `brands/[brandSlug].tsx:60-84` + `BrandDetailContent.tsx:24`, `articles/[articleSlug].tsx:53-56` + `ArticleDetailContent.tsx:17`, `blogArticles/[blogArticleSlug].tsx:58-70` + `BlogArticleDetailContent.tsx:33` + `ArticleMetadata` props, `blogCategories/[blogCategorySlug].tsx:37-58`; `useSeo.ts:29-31` (`seoPage.seo.title` …).
- `make generate-schema`, `pnpm run typecheck`.

#### 8. Fixtures a testy (přejmenování)
- `project-base/app/src/DataFixtures/Demo/{BlogArticle,Brand,Category,Product,SeoPage}DataFixture.php` → `$data->seo[$domainId]->title = …`.
- `project-base/app/tests/App/Functional/Model/{Category/CategoryDomainTest,Product/Brand/BrandDomainTest,Product/ProductDomainTest,SeoPage/SeoPageTest}.php`, GraphQL testy dotazující plochá pole (grep `seoTitle` v `project-base/app/tests/FrontendApiBundle/**/*.graphql`).
- Překlady: `phing translations-dump` (labely se nemění, jen kontrola).

### Success Criteria
#### Automated Verification
- [ ] `docker compose exec php-fpm php phing clean db-migrations db-check-mapping annotations-check` bez diffu
- [ ] `docker compose exec php-fpm php phing standards-fix phpstan`
- [ ] `docker compose exec php-fpm php phing tests-unit tests` (vč. `ProductExportRepositoryTest` — ES klíče beze změny)
- [ ] `make generate-schema` + `make check-schema`; `docker compose exec storefront pnpm run check test--no-watch`
- [ ] `docker compose exec php-fpm php phing elasticsearch-export` proběhne (gettery-delegáty)
#### Manual Verification
- [ ] Admin formuláře produktu, kategorie, brandu, článku, blog článku, blog kategorie, SEO mixu a SEO page zobrazují SEO skupinu s původními hodnotami; uložení funguje
- [ ] Storefront: title/description/h1 na detailech beze změny oproti `20.0`

---

## Phase 2: Nové atributy — meta robots a canonical URL

### Overview
Enum, sloupce `seo_meta_robots`/`seo_canonical_url` ve všech tabulkách (pokud je nepřidala Phase 1), pole ve formuláři, validace + JS, ES export, fallback description v API.

### Changes Required
1. **Enum** `packages/framework/src/Model/Seo/SeoMetaRobotsEnum.php` (dle `.claude/skills/create-enum`): `NOINDEX = 'noindex'`, `NOFOLLOW = 'nofollow'`, `NOINDEX_NOFOLLOW = 'noindex, nofollow'`; `getAllIndexedByTranslations()`; `getNoindexCases()`; `isNoindex(?string)`.
2. **Migrace** `Version<ts>.php` (pokud sloupce nepřidala Phase 1): pro `product_domains`, `category_domains`, `blog_article_domains`, `blog_category_domains`, `brand_domains`, `articles`, `ready_category_seo_mixes`, `seo_page_domains` (jen `seo_meta_robots`): `ADD seo_meta_robots VARCHAR(30) DEFAULT NULL, ADD seo_canonical_url TEXT DEFAULT NULL`.
3. **Formulář** `SeoAttributesFormType`:
```php
$builder->add('metaRobots', ChoiceType::class, [
    'required' => false,
    'label' => 'Meta robots',
    'choices' => $this->seoMetaRobotsEnum->getAllIndexedByTranslations(),
    'placeholder' => t('Default (not set)'),
    'constraints' => [new Constraints\Choice(choices: $this->seoMetaRobotsEnum->getAllCases())],
    'help' => t('When left empty, the storefront keeps its built-in behavior for this page type.'),
]);
$builder->add('canonicalUrl', UrlType::class, [
    'required' => false,
    'label' => 'Canonical URL',
    'default_protocol' => null,
    'constraints' => [new Constraints\Url(protocols: ['https'], message: 'Canonical URL must be an absolute https:// URL')],
    'attr' => [], // data-js-canonical-domain-url dodá formulář přes options_by_field_name
]);
```
   Formuláře předají `data-js-canonical-domain-url` (`$domainConfig->getUrl()`) přes `options_by_field_name['canonicalUrl']['attr']` — multidoménově v `options_by_domain_id[$domainId]`, single-domain v `seo_attributes_options`.
4. **Admin JS** `packages/administration/assets/src/js/utils/canonicalUrlCrossDomainWarning.js` (vzor `recommendedLength.js`): na `input` u `input[data-js-canonical-domain-url]` porovnat `new URL(value).host` s hostem atributu, zobrazit/skrýt `<span class="text-warning small ms-3">` s `Translator.trans('This is a cross-domain canonical URL – it points to a different domain than the one being edited.')`; registrace přes `Register`, import v `packages/administration/assets/src/js/index.js:9-12`.
5. **Fallback description** — `TransformStringHelper::truncateToWholeWords(string $text, int $maxLength): string` (mb_*, řez na poslední mezeře, bez ellipsis, ořez trailing interpunkce) a v `SeoAttributesResultFactory`:
```php
public const int META_DESCRIPTION_MAX_LENGTH = 160;

protected function resolveMetaDescription(?string $metaDescription, ?string $fallbackHtml): ?string
{
    $trimmed = TransformStringHelper::getTrimmedStringOrNullOnEmpty($metaDescription);
    if ($trimmed !== null) {
        return $trimmed;
    }
    $plainText = TransformStringHelper::getTrimmedStringOrNullOnEmpty(TransformStringHelper::convertHtmlToPlainText($fallbackHtml));

    return $plainText === null ? null : TransformStringHelper::truncateToWholeWords($plainText, self::META_DESCRIPTION_MAX_LENGTH);
}
```
   Zdroje fallbacku: Category `getDescription($domainId)`; SEO mix `getDescription() ?? category description`; Brand `getDescription($locale)`; BlogCategory `getDescription($locale)`; Product `description` (ES `$data['description']` / entita); BlogArticle `$data['perex']`; Article `$data['text']`; SeoPage bez fallbacku; Store `getDescription()` (Phase 3); Flag bez fallbacku.
6. **ES export** — `ProductExportFieldProvider.php:42-44` `SEO_META_ROBOTS = 'seo_meta_robots'`, `SEO_CANONICAL_URL = 'seo_canonical_url'`; `ProductExportRepository.php:164-166`; `ProductExportScopeConfig.php:155` (SEO scope); `BlogArticleExportRepository.php:98-100` + `BlogArticleElasticsearchDataFetcher.php` (`seoMetaRobots`, `seoCanonicalUrl`); `ArticleExportRepository.php:70-72` + `ArticleElasticsearchDataFetcher.php`; definice `project-base/app/src/Resources/definition/{product,blog_article,article}/{1,2,3}.json` (`keyword`; canonical `"index": false`); `ProductExportRepositoryTest.php:95-160`.
7. `make generate-schema` (schema typu se nemění, generated soubory po změně fragmentu ano).
8. **Fixtures**: `SeoPageDataFixture.php` — `first_demo_seo_page` `seo[$domainId]->metaRobots = 'noindex, nofollow'`; `BrandDataFixture.php` — jeden brand `canonicalUrl` (brand není v sitemapě).
9. **Překlady** `phing translations-dump` + cs/sk `msgstr`.

### Success Criteria
#### Automated Verification
- [ ] `phing db-migrations db-check-mapping`, `phing standards-fix phpstan`
- [ ] Unit: `packages/framework/tests/Unit/Component/String/TransformStringTest.php` (truncate: kratší text, řez na mezeře, dlouhé slovo, mb znaky, interpunkce), `packages/frontend-api/tests/Unit/Model/Seo/SeoAttributesResultFactoryTest.php` (admin hodnota vítězí, HTML → text, 160, prázdné → null), `packages/framework/tests/Unit/Model/Seo/SeoMetaRobotsEnumTest.php`
- [ ] Funkční GraphQL: kategorie bez meta description vrací zkrácený popis; SeoPage `seo.metaRobots`; blog článek perex (po `test-elasticsearch-index-recreate test-elasticsearch-export`)
- [ ] `phing elasticsearch-index-recreate elasticsearch-export`, `make check-schema`, `phing npm`
#### Manual Verification
- [ ] Robots select s „Default (not set)“; canonical `http://` odmítnut; `https://jina-domena.cz/x` uloží + JS varování
- [ ] GraphQL produkt bez meta description → plain text ≤160 bez useknutého slova

---

## Phase 3: Store a Flag dostanou SEO atributy

### Changes Required
- **Store** (single-domain): `Store.php` `#[ORM\Embedded] protected $seo` + `getSeoAttributes()`; `StoreData::$seo` (`SeoAttributesData`), `StoreDataFactory::createForDomain()/createFromStore()`; `StoreFormType.php` `->add('seoGroup', SeoGroupType::class, ['multidomain' => false, 'seo_attributes_options' => ['options_by_field_name' => ['canonicalUrl' => ['attr' => ['data-js-canonical-domain-url' => …]]]], 'url_list_options' => $store !== null ? [route_name, entity_id, limit_domains_by_ids] : null])` za `userInformation` (URL domény z `$store?->getDomainId()` / `$options['domain_id']` — ověřit, jak `StoreController` předává doménu), `urls` odebrat z `basicInformationGroup:92`; migrace `ALTER TABLE stores ADD seo_title TEXT, ADD seo_meta_description TEXT, ADD seo_h1 TEXT, ADD seo_meta_robots VARCHAR(30), ADD seo_canonical_url TEXT` (vše NULL); `StoreDecorator.types.yaml` `interfaces: ['Seo']` + `seo`; `StoreResolverMap.php:23` `'seo' => factory->createFromSeoAttributes($store->getSeoAttributes(), $store->getDescription())`; `StoreDataFixture.php` `ATTR_SEO` pro Ostravu.
- **Flag**: nová `FlagDomain.php` (vzor `BrandDomain.php`; tabulka `flag_domains`, unique `(flag_id, domain_id)`, FK CASCADE, `#[ORM\Embedded] $seo`); `Flag.php` `$domains` OneToMany + `createDomains()/setDomains()` + `getSeoAttributes(int $domainId)` + `FlagDomainNotFoundException`; `FlagData::$seo = []`; `FlagDataFactory.php:31-63` loop `$this->domain->getAllIds()`; `FlagFormType.php:59-70` → `SeoGroupType` vždy (`url_list_options` jen při editaci, jinak `null`); migrace `CREATE TABLE flag_domains …` + backfill `INSERT INTO flag_domains (flag_id, domain_id) SELECT id, :domainId FROM flags` per doména (`MultidomainMigrationTrait`); `FlagDecorator.types.yaml` `Seo` + `seo`; `FlagResolverMap.php` `'seo'`; `FlagDataFixture.php` SEO pro jeden flag (loop `getAllowedDemoDataDomains()` jako `BrandDataFixture.php:59-81`).
- `make generate-schema`; storefront fragmenty `StoreDetailFragment.graphql`, `FlagDetailFragment.graphql` + `seo { ...SeoAttributesFragment }`; `pages/stores/[storeSlug].tsx:34-44` `title={store.seo.title || getPrefixedSeoTitle(store.storeName, t('Store'))}`, `description={store.seo.metaDescription}`; `StoreDetailContent.tsx:58` `<h1>{store.seo.h1 || store.storeName}</h1>`; `pages/flags/[flagSlug].tsx:58,72` `useSeoTitleWithPagination(totalCount, flag.name, flag.seo.title)`, `description`; `FlagDetailContent.tsx:22` h1 `flag.seo.h1`.

### Success Criteria
#### Automated Verification
- [ ] `phing db-migrations db-check-mapping standards-fix phpstan`
- [ ] Nový `project-base/app/tests/App/Functional/Model/Product/Flag/FlagFacadeTest.php` (create/edit SEO per doména), `tests/FrontendApiBundle/Functional/Store/GetStoreTest.php` rozšířen o `seo` + fallback z description, nový `Flag/FlagSeoTest.php`
- [ ] `make check-schema`, `pnpm run check`
#### Manual Verification
- [ ] Admin Store/Flag mají SEO skupinu; storefront prodejna „Prodejna Ostrava | Demo eshop“ + description z popisu; flag bez popisu bez description tagu

---

## Phase 4: Sitemapa

### Changes Required
- `SitemapRepository.php` — inject `SeoMetaRobotsEnum`; helper:
```php
/**
 * Pages with a canonical URL or noindex robots must not be offered to search engines in the sitemap
 */
protected function addSeoExclusionConditions(QueryBuilder $queryBuilder, string $alias): void
{
    $queryBuilder
        ->andWhere(sprintf('%s.seo.canonicalUrl IS NULL', $alias))
        ->andWhere(sprintf('%1$s.seo.metaRobots IS NULL OR %1$s.seo.metaRobots NOT IN (:noindexRobots)', $alias))
        ->setParameter('noindexRobots', $this->seoMetaRobotsEnum->getNoindexCases());
}
```
  použít: produkty `pd` (`:34-53`, `:145-167`), kategorie `cd` (`:58-76`), články `a` (`:81-99`), blog články `bad` (`:122-143`), flagy — `->join('f.domains', 'fd', Join::WITH, 'fd.domainId = :domainId')` (`:172-190`), SEO mixy `rcsm` (`:195-214`).
- Test `project-base/app/tests/App/Functional/Model/Sitemap/SitemapTest.php` (vzor `ImageSitemapTest.php`): produkt s canonical + kategorie s `noindex` (edit přes project-base facade), `SitemapFacade::generateForAllDomains()`, assert nepřítomnost/přítomnost slugů; ošetřit izolaci dat (transakce vs. `ApplicationTestCase`).

### Success Criteria
#### Automated Verification
- [ ] `phing tests` (`SitemapTest`), `phing standards-fix phpstan`
- [ ] `bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Sitemap\SitemapCronModule"` doběhne
#### Manual Verification
- [ ] Kategorie s `noindex` chybí v `web/content/sitemaps/domain_1_sitemap.categories*.xml`

---

## Phase 5: Settings > SEO — odstranění Headline a Meta description

### Changes Required
1. `SeoSettingFacade.php` — smazat `SEO_TITLE_MAIN_PAGE`, `SEO_META_DESCRIPTION_MAIN_PAGE`, `getTitleMainPage()`, `getDescriptionMainPage()`, `getDescriptionsMainPageIndexedByDomainIds()`, `setTitleMainPage()`, `setDescriptionMainPage()`.
2. `SeoController.php:31-62` jen `titleAddOn`; `SeoSettingFormType.php` bez `title`/`metaDescription` (+ import `TextareaType`).
3. `RequiredSettingExtension.php:94,329-353` — smazat `checkSeoInformationIsSet()` a injekci facade.
4. `SeoExtension.php:29,45-50` — smazat `getSeoMetaDescription`.
5. FE API `SeoSettingDecorator.types.yaml:11-19` smazat `title`, `metaDescription`; `SeoSettingsQuery.php:20-29`.
6. Migrace (`DomainAwareInterface` + `MultidomainMigrationTrait`): per doména `UPDATE seo_page_domains SET seo_title = COALESCE(seo_title, sv.value) FROM setting_values sv WHERE sv.name = 'seoTitleMainPage' AND sv.domain_id = :domainId AND seo_page_domains.domain_id = :domainId AND page_slug = '/'` (totéž `seo_meta_description` ← `seoMetaDescriptionMainPage`), poté `DELETE FROM setting_values WHERE name IN ('seoTitleMainPage', 'seoMetaDescriptionMainPage')`.
7. `SettingValueDataFixture.php:109-118` smazat; `SeoSettingTest.php` upravit; `docs/model/domain-limiting.md:82-98` snippet.
8. Storefront: `useSeo.ts:35-36,43,45` bez settings fallbacků; `SeoSettingFragment.graphql` bez `title`/`metaDescription`; explicitní `title`: `pages/contact-form.tsx` (`t('Contact form')`), `pages/personal-data-overview/[hash].tsx` (`t('Personal data overview')`), `pages/customer/order-detail.tsx` (`${t('Order number')} ${order.number}`); homepage bez explicitního titulku — `SeoMeta` skládá `<title>` jako `[title, titleSuffix].filter(Boolean).join(' ')`, log „Missing required tags“ jen když jsou obě části prázdné.
9. `translations-dump` (osiřelé msgid), `make generate-schema`.

### Success Criteria
#### Automated Verification
- [ ] `phing db-migrations` (demo DB: hodnoty v `seo_page_domains` pro `/`, `setting_values` bez obou názvů)
- [ ] `phing tests` (`SeoSettingTest`, smoke `admin_seo_index`), `make check-schema`, `pnpm run typecheck`, `phing standards-fix phpstan`
#### Manual Verification
- [ ] *Settings > SEO > SEO* jen „Complement to title“; required-settings lišta bez SEO hlášky; homepage `<title>` = SeoPage `/` + suffix

---

## Phase 6: SEO pages pro statické stránky storefrontu (čistá instalace)

### Changes Required
Migrace `Version<ts>.php` (`DomainAwareInterface` + `MultidomainMigrationTrait`), zdroj pravdy `project-base/storefront/config/routes.ts` (en/cs/sk, jiné locale → en):

| page_name | en | cs | sk | robots | legacy slugy |
|---|---|---|---|---|---|
| Homepage | `/` | `/` | `/` | — | existuje |
| Nákupní košík | `cart` | `kosik` | `kosik` | `noindex` | existuje |
| Napište nám | `contact-form` | `kontaktni-formular` | `kontaktny-formular` | — | `contact`, `kontakt` |
| Zapomenuté heslo | `reset-password` | `zapomenute-heslo` | `zabudnute-heslo` | — | `forgot-password` |
| Registrace | `registration` | `registrace` | `registracia` | — | sk `registrace` |
| Prodejny | `stores` | `obchodni-domy` | `obchodne-domy` | — | sk `obchodni-domy` |
| Značky | `brands-overview` | `prehled-znacek` | `prehlad-znaciek` | — | `brands` |
| Přihlášení | `login` | `prihlaseni` | `prihlasenie` | — | sk `prihlaseni` |
| Souhlas se soubory cookies | `user-consent` | `uzivatelsky-souhlas` | `pouzivatelsky-suhlas` | — | `cookie-consent`, `souhlas-se-soubory-cookies` |
| Catalog | `catalog` | `katalog` | `katalog` | — | existuje |
| Vyhledávání | `search` | `hledani` | `hladanie` | `noindex, nofollow` | nový |
| Nové heslo | `new-password` | `nove-heslo` | `nove-heslo` | `noindex` | nový |
| Oblíbené produkty | `wishlist` | `oblibene-produkty` | `oblubene-produkty` | — | nový |
| Porovnání produktů | `product-comparison` | `porovnani-produktu` | `porovnanie-produktov` | — | nový |
| Export osobních údajů | `personal-data-export` | `export-osobnich-udaju` | `export-osobnych-udajov` | `noindex` | nový |
| Přehled osobních údajů | `personal-data-overview` | `prehled-osobnich-udaju` | `prehlad-osobnych-udajov` | `noindex` | nový |
| Objednávka – doprava a platba | `order/transport-and-payment` | `objednavka/doprava-a-platba` | `objednavka/doprava-a-platba` | `noindex` | nový |
| Objednávka – kontaktní údaje | `order/contact-information` | `objednavka/kontaktni-udaje` | `objednavka/kontaktne-udaje` | `noindex` | nový |
| Potvrzení objednávky | `order-confirmation` | `potvrzeni-objednavky` | `potvrdenie-objednavky` | `noindex` | nový |
| Zákazník – objednávky | `customer/orders` | `zakaznik/objednavky` | `zakaznik/objednavky` | `noindex` | nový |
| Zákazník – detail objednávky | `customer/order-detail` | `zakaznik/detail-objednavky` | `zakaznik/detail-objednavky` | `noindex` | nový |
| Zákazník – reklamace | `customer/complaints` | `zakaznik/reklamace` | `zakaznik/reklamacie` | `noindex` | nový |
| Zákazník – detail reklamace | `customer/complaint-detail` | `zakaznik/detail-reklamace` | `zakaznik/detail-reklamacie` | `noindex` | nový |
| Zákazník – nová reklamace | `customer/new-complaint` | `zakaznik/nova-reklamace` | `zakaznik/nova-reklamacia` | `noindex` | nový |
| Zákazník – úprava údajů | `customer/edit-profile` | `zakaznik/upravit-udaje` | `zakaznik/upravit-udaje` | `noindex` | nový |
| Zákazník – změna hesla | `customer/change-password` | `zakaznik/zmenit-heslo` | `zakaznik/zmenit-heslo` | `noindex` | nový |
| Zákazník – uživatelé | `customer/users` | `zakaznik/uzivatele` | `zakaznik/pouzivatelia` | `noindex` | nový |

Vynechány technické: `styleguide`, `_feedback`, `grapesjs-template`, `social-login`, `test-errors`.

Algoritmus per stránka × doména (idempotentní): (1) `expectedSlug` dle locale; (2) existuje `(domain_id, page_slug = expectedSlug)` → jen doplnit `seo_meta_robots`, je-li NULL a default definován; (3) jinak řádek téže `page_name` na doméně se slugem z legacy/jiné locale → `UPDATE page_slug` (+ robots je-li NULL); (4) jinak najít/vložit `seo_pages` (`default_page = true`) a vložit `seo_page_domains`. Friendly URL `front_page_seo` nevkládat. Regex slugu (`SeoPageFormType.php:101-105`) povoluje `/`.

`SeoPageDataFixture.php:74-118` — `fillSeoPageData()` nesmí přepsat `metaRobots` z migrace (vychází z `createFromSeoPage`). Test `tests/App/Functional/Model/SeoPage/SeoPageTest.php`: `cart`/`kosik` má `noindex`, `search` `noindex, nofollow`, `wishlist` existuje.

### Success Criteria
#### Automated Verification
- [ ] `phing db-migrations` na demo DB i `phing db-create db-migrations` na prázdné; `phing tests`; `phing standards-fix phpstan`
#### Manual Verification
- [ ] *Settings > SEO > SEO pages* obsahuje všechny stránky z tabulky per doména se správnými slugy

---

## Phase 7: Storefront — robots, canonical, hreflang, sjednocené `seo`

### Changes Required
1. `types/seo.ts` — `MetaRobotsContent` union (přesun z `MetaRobots.tsx`).
2. Utily `utils/seo/isNoindexMetaRobots.ts` (`split(',')` a `trim() === 'noindex'`), `utils/seo/resolveMetaRobots.ts` (SeoPage → entita → default), `utils/seo/getDocumentTitle.ts`.
3. `useSeo.ts` — vstupy `seo?: TypeSeoAttributesFragment | null` (entita), `defaultMetaRobots`, `canonicalQueryParams`; `metaRobots = resolveMetaRobots(seoPage?.seo.metaRobots, seo?.metaRobots, defaultMetaRobots)`; `canonicalUrl = seoPage?.seo.canonicalUrl || seo?.canonicalUrl || generateCanonicalUrl(...)`; `title = seoPage?.seo.title ?? defaultTitle ?? ''`, `description = seoPage?.seo.metaDescription ?? defaultDescription ?? null`; vrací `isNoindex`.
4. `SeoMeta.tsx` — props `seo`, `defaultMetaRobots`; `<title>{getDocumentTitle(title, titleSuffix)}</title>`; `{metaRobots && <meta name="robots" content={metaRobots} />}`; canonical, hreflang map i `x-default` jen `!isNoindex`. `CommonLayout.tsx:45-54,115-122` props `seo`, `defaultMetaRobots`. `OrderLayout.tsx:24` `defaultMetaRobots="noindex"`. Smazat `MetaRobots.tsx`.
5. Nahradit `<MetaRobots>` (24 souborů): statické → `defaultMetaRobots="noindex"` (`search.tsx` `"noindex, nofollow"`; `order/*` řeší `OrderLayout`); podmíněné `categories/[categorySlug].tsx:63`, `brands/[brandSlug].tsx:75`, `flags/[flagSlug].tsx:65` → `defaultMetaRobots={currentFilter || currentSort ? 'noindex, follow' : undefined}`; `blogArticles/[blogArticleSlug].tsx:53` → `shouldNoIndex ? 'noindex, nofollow' : undefined`.
6. Entity stránky předají `seo={entity.seo}` — `SeoMeta` z něj čte **jen** `metaRobots` a `canonicalUrl` (viz „Dělba zodpovědností BE × SF“); `title`/`description` props zůstávají a stránka si je dál skládá sama (`seo.title || name`, prefixy, `useSeoTitleWithPagination`).
7. **H1 ze SeoPage na statických stránkách**: malý hook `utils/seo/useSeoPageH1.ts` (`(defaultH1: string) => seoPage?.seo.h1 ?? defaultH1` — reuse `useSeoPageQuery` se slugem z routeru, dotaz už běží ze `SeoMeta`, urql cache → bez requestu navíc); použít v komponentách se statickým `<h1>`: `CartContent.tsx:24`, `ProductComparison.tsx:53`, `Wishlist.tsx:50`, `PersonalDataDetailContent.tsx:30`, `TransportAndPaymentContent.tsx:45`, `ContactInformationContent.tsx:25`. `SearchPageContent.tsx:35` nechat (dynamický nadpis s dotazem); stránky bez `<h1>` zůstávají bez něj.
8. `pnpm run translate` (+ cs/sk), flush Redis `*fe:translates*`; `make generate-schema`.
9. Testy: vitest `vitest/utils/seo/{isNoindexMetaRobots,resolveMetaRobots,getDocumentTitle}.test.ts`; Cypress `cypress/e2e/ssr/serverSideRendering.cy.ts` — produkt: bez `<meta name="robots"`, s `rel="canonical"`; `/search`: robots `noindex, nofollow`, **bez** canonical i hreflang.

### Success Criteria
#### Automated Verification
- [ ] `pnpm run check`, `pnpm run test--no-watch`, `pnpm run knip`, `make check-schema`, Cypress SSR suite
#### Manual Verification
- [ ] Produkt s canonical z adminu → canonical z adminu; kategorie `noindex` → robots, bez canonical/hreflang; kategorie `?sort=` → `noindex, follow`; prodejna „Prodejna Ostrava | Demo eshop“ + description; flag bez description tagu; homepage title ze SeoPage `/`

---

## Phase 8: Upgrade notes a dokumentace

### Changes Required
- `upgrade-notes/backend_<YYYYMMDD_HHMMSS>.md`: `SeoAttributes` embeddable + `*Data::$seo` (**BC break**: `seoTitles`/`seoMetaDescriptions`/`seoH1s`, `SeoPageData::*IndexedByDomainId`, `ReadyCategorySeoMixData::title/metaDescription/h1` → `seo`), formuláře přes `SeoAttributesFormType`/`SeoGroupType` (projekty s vlastní úpravou SEO skupiny si napíší extension pro `SeoAttributesFormType`; skrývání H1 v `ProductFormTypeExtension` odstraněno), přejmenované sloupce SeoMix/SeoPage, nová tabulka `flag_domains`, **reindex ES** (`elasticsearch-index-recreate` + `elasticsearch-export`), GraphQL: odstraněná plochá pole + nový interface `Seo`/typ `SeoAttributes`, `settings.seo.title/metaDescription` odstraněny (projekty s vlastním FE → `seoPage(pageSlug: "/")`), odstraněné metody/konstanty `SeoSettingFacade`, Twig `getSeoMetaDescription`, DQL `bd.seo.title` (projekty s vlastními dotazy), kontrola *Settings > SEO > SEO pages*, hint 155→160. Změny konstruktorů neuvádět.
- `upgrade-notes/storefront_<…>.md`: `MetaRobots` → `defaultMetaRobots`; `seo { … }` fragment místo plochých polí; `settings.seo.title/metaDescription` pryč; „see #project-base-diff“.
- Docs: `docs/model/seo-attributes.md` (embeddable, Data, form type, GraphQL, fallback pravidla, robots/canonical/hreflang/sitemap, SEO pages) + `docs/navigation.yml`; `docs/model/domain-limiting.md:94`.

### Success Criteria
- [ ] `phing markdown-check`; notes pokrývají BC breaky z Phase 1, 2, 3, 5, 6 a storefront z Phase 7

---

## Testing Strategy
- **Unit**: `truncateToWholeWords`, `SeoAttributesResultFactory` (fallback), `SeoMetaRobotsEnum::isNoindex`, storefront utily.
- **Funkční**: refaktor Phase 1 kryjí stávající `*DomainTest`, `SeoPageTest`, GraphQL testy; nové: Store/Flag `seo`, kategorie fallback, SeoPage robots, `SitemapTest`, `FlagFacadeTest`, `SeoSettingTest`, `ProductExportRepositoryTest`.
- **Cypress**: SSR asserty robots/canonical/hreflang.
- **Manuální**: viz kritéria fází; celkový průchod: `phing db-demo elasticsearch-index-recreate elasticsearch-export` → admin nastavení → SSR curl → sitemap cron → SEO pages.

## Performance Considerations
- Embeddable nepřidává joiny ani dotazy; fallback description je `strip_tags`/`mb_*` per detail.
- Sitemap DQL přidává dvě WHERE podmínky bez indexu — dávkový cron, akceptovatelné.
- Nová ES pole `keyword` — zanedbatelné.

## Migration Notes
- Migrace ve `packages/framework/src/Migrations/`: (1) rename sloupců SeoMix/SeoPage + `seo_h1` nullable (+ případně nové sloupce), (2) `seo_meta_robots`/`seo_canonical_url` + `stores` SEO sloupce + `flag_domains` s backfillem, (3) settings → SeoPage `/` + delete, (4) SEO pages seed. Všechny idempotentní.
- Po nasazení: reindex ES, regenerace sitemapy cronem, Redis flush storefront překladů, kontrola SEO pages.
- Rebase na `20.0` po merge PR #4810.

## References
- Jira SSP-3082 (`mcp__atlassian__getJiraIssue`); SSP-948 (Done), SSP-4275 / PR #4810 (otevřený)
- Vzory: `BrandDomain.php`, `Brand.php:128-148`, `SeoPageFormType.php:113-163`, `MultidomainType.php:31-46`, `HreflangDecorator.types.yaml`, `recommendedLength.js`, `Version20250119100000.php`, `Version20240426045700.php`, `ImageSitemapTest.php`, `GetStoreTest.php`, `useSeo.ts`, `SeoMeta.tsx`, `config/routes.ts`, `McpEntityExposureAttributeRule.php:212`
