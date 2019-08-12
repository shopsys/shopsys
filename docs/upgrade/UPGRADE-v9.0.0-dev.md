# [Upgrade from v8.1.0-dev to v9.0.0-dev](https://github.com/shopsys/shopsys/compare/HEAD...9.0)

This guide contains instructions to upgrade from version v8.1.0-dev to v9.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Infrastructure
- follow infrastructure instructions in the [separate article](/docs/upgrade/upgrade-instructions-for-exporting-of-products-to-elasticsearch-asynchronously.md#infrastructure) to add RabbitMQ ([#1321](https://github.com/shopsys/shopsys/pull/1321))

### Application

- update your twig files ([#1284](https://github.com/shopsys/shopsys/pull/1284/)):
    - `src/Shopsys/ShopBundle/Resources/views/Front/Content/Product/list.html.twig`
        - remove: `{% import '@ShopsysShop/Front/Content/Product/productListMacro.html.twig' as productList %}`
    - `src/Shopsys/ShopBundle/Resources/views/Front/Content/Product/listByBrand.html.twig`
        - remove: `{% import '@ShopsysShop/Front/Content/Product/productListMacro.html.twig' as productList %}`
    - `src/Shopsys/ShopBundle/Resources/views/Front/Content/Product/productListMacro.html.twig`
        - remove: `{% import '@ShopsysShop/Front/Inline/Product/productFlagsMacro.html.twig' as productFlags %}`
    - `src/Shopsys/ShopBundle/Resources/views/Front/Content/Product/search.html.twig`
        - remove: `{% import '@ShopsysShop/Front/Content/Product/productListMacro.html.twig' as productList %}`
    - check your templates if you are extending or importing any of the following templates as imports of unused macros were removed from them:
        - `src/Resources/views/Admin/Content/Article/detail.html.twig`
        - `src/Resources/views/Admin/Content/Brand/detail.html.twig`
        - `src/Resources/views/Admin/Content/Category/detail.html.twig`
        - `src/Resources/views/Admin/Content/Product/detail.html.twig`
- follow application instructions in the [separate article](/docs/upgrade/upgrade-instructions-for-exporting-of-products-to-elasticsearch-asynchronously.md#application) to use RabbitMQ to export Products to Elasticsearch ([#1321](https://github.com/shopsys/shopsys/pull/1321))

[shopsys/framework]: https://github.com/shopsys/framework
