# SEO Attributes

Every entity with its own storefront page (products, categories, brands, blog articles, stores, etc.) shares one set of SEO attributes: page title, meta description, heading (H1), meta robots, and canonical URL.
The attributes are edited in the same form group in the administration, exposed by the Frontend API in the same `seo` field, and rendered by the storefront with the same rules.

**Table of Contents:**

[TOC]

## Model

The attributes are stored in the [`SeoAttributes`]({{github.link}}/packages/framework/src/Model/Seo/SeoAttributes.php) Doctrine embeddable, embedded as `$seo` in the entity (or in its domain entity for multidomain entities), and transferred in [`SeoAttributesData`]({{github.link}}/packages/framework/src/Model/Seo/SeoAttributesData.php) as `*Data::$seo`.

```php
#[ORM\Embedded(class: SeoAttributes::class)]
protected $seo;
```

The allowed values of meta robots are defined in [`SeoMetaRobotsEnum`]({{github.link}}/packages/framework/src/Model/Seo/SeoMetaRobotsEnum.php), `null` means the storefront decides.

## Administration

All SEO forms use [`SeoGroupType`]({{github.link}}/packages/framework/src/Form/Admin/Seo/SeoGroupType.php), the "SEO" card of the entity form.
The card is composed of standard building blocks: [`DomainSeoType`]({{github.link}}/packages/framework/src/Form/Admin/Seo/DomainSeoType.php) holds the SEO attributes ([`SeoAttributesType`]({{github.link}}/packages/framework/src/Form/Admin/Seo/SeoAttributesType.php)) and the URL addresses (`UrlListType`) of one domain, and `SeoGroupType` either renders one `DomainSeoType` per domain enabled in the administration via `MultidomainType`, or a single one for an entity that belongs to one domain.

```php
$builderSeoGroup = $builder->create('seoGroup', SeoGroupType::class, [
    'placeholder_source_path' => ['basicInformation', 'name'],
    'url_list_options' => $brand !== null ? [
        'route_name' => 'front_brand_detail',
        'entity_id' => $brand->getId(),
    ] : null,
]);
```

- `domain_id` — `null` (default) renders a card for every domain enabled in the administration and maps the fields to `$seo[$domainId]` and `$urls[$domainId]` of the entity data, an integer renders a single card for a single-domain entity (e.g. `Store`) and maps the fields to `$seo` and `$urls`
- `placeholder_source_path` — names of the nested fields leading from the form root to the name input whose value is shown as a live placeholder of the title and H1 (the `{locale}` and `{domain_id}` tokens are replaced per domain, e.g. `['name', '{locale}']` for a `LocalizedType` name); a wrong path fails when the form is rendered
- `h1_required` — `false` by default
- `url_list_options` — options passed to `UrlListType` (`route_name`, `entity_id`), `null` renders no URL addresses

Title and meta description show the recommended length (60 and 160 characters).
Projects that need to change the fields do it once in a form type extension of `SeoAttributesType`.

### SEO pages

Static storefront pages (homepage, cart, search, customer section, ...) have no entity, their SEO attributes are stored in `SeoPage` (_Settings > SEO > SEO pages_).
The slug of a SEO page has to match the storefront route (`config/routes.ts` in the storefront), the homepage is the SEO page with the slug `/`.
SEO pages additionally carry the Open Graph title, description and image.

The title add-on appended to every page title stays in _Settings > SEO_.

## Frontend API

Every GraphQL type with SEO attributes implements the `Seo` interface and exposes `seo: SeoAttributes!` (`title`, `metaDescription`, `h1`, `metaRobots`, `canonicalUrl`) exactly as set in the administration — the API does not apply any fallbacks, that is the responsibility of the storefront.
The resolvers of entities return the `SeoAttributes` embeddable itself, the resolvers reading Elasticsearch build [`SeoAttributesQueryDto`]({{github.link}}/packages/frontend-api/src/Model/Seo/SeoAttributesQueryDto.php) with [`SeoAttributesQueryDtoFactory`]({{github.link}}/packages/frontend-api/src/Model/Seo/SeoAttributesQueryDtoFactory.php):

```php
'seo' => fn (Category $category) => $category->getSeoAttributes($this->domain->getId()),
```

## Storefront

The API returns the attributes exactly as set in the administration, all the fallbacks live on the storefront. Pages pass the `seo` field of the entity together with their defaults (`defaultTitle`, `defaultDescription`, `defaultMetaRobots`) to `CommonLayout`, the `SeoMeta` component then renders the whole `<head>` part through the `useSeo()` hook, which is the only place deciding the precedence.

- `<title>` is the title of the SEO page, then `seo.title`, then `seo.h1` (so that e.g. a category SEO mix keeps its heading in the title) and finally `defaultTitle` (usually the name of the entity), followed by the title add-on. Brand and store pages prefix the name with the entity type (`getPrefixedSeoTitle()`).
- H1 is `seo.h1` or the name of the entity. Static pages use `useSeoPageH1()` to let the SEO page override their hardcoded heading.
- Meta description is the one of the SEO page, then `seo.metaDescription`, then the plain text of `defaultDescription` (the HTML description of the entity, the perex of a blog article or its text when the perex is empty, the text of an article) truncated to 160 characters at a word boundary (`getMetaDescription()`).
- Paginated listings append the current page to both the title and the H1 with `useHeadingWithPagination()` (e.g. "Electronics page 2 from 5"). The title gets the suffix inside `useSeo()` from the `paginationTotalCount` and `paginationPageSize` props of `CommonLayout`, so it is appended to whichever title wins.
- Meta robots are resolved in the order SEO page → entity → storefront default (`resolveMetaRobots()`). The value set in the administration is the source of truth, the storefront default (`noindex` for cart, checkout, customer section, search, filtered or sorted listings and blog article drafts, passed as `defaultMetaRobots` of `CommonLayout`) applies only when the administrator leaves _Default (not set)_.
- Canonical URL is the one of the SEO page, then of the entity, then the generated self-canonical (`generateCanonicalUrl()`, which keeps only the whitelisted query parameters). On a `noindex` page neither canonical nor hreflang links (including `x-default`) are rendered, as the combination would send contradictory signals to the crawlers.

The helpers live in `utils/seo/` of the storefront.

## Sitemap

Entities with `noindex` in meta robots or with a canonical URL set are excluded from the sitemap, see `SitemapRepository::addSeoExclusionConditions()`.
