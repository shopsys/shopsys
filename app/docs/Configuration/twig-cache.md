# Twig cache

Twig templates are by default cached in all application environments except development.
You can override default behavior by setting environment variable `TWIG_CACHE_ENABLED` either in `.env(.local|.prod.local)` file or by setting the real environment variable (on the CI).

## Caching in templates

You can leverage existing Twig function `getCacheLifetimeByHours` which returns proper value to set lifetime by hours (you don't need to multiply seconds in the template anymore)

Example

```twig
{% cache constant('App\\Twig\\Cache\\TwigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE') {lifetime:getCacheLifetimeByHours(4), domainId:getDomain().id} %}
    {{ render(controller('App\\Controller\\Front\\HomepageController:slightlyChangingPartsOnHomepageAction'))|spaceless }}
{% endcache %}
```
