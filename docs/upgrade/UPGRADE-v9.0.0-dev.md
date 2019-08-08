# [Upgrade from v8.1.0-dev to v9.0.0-dev](https://github.com/shopsys/shopsys/compare/HEAD...9.0)

This guide contains instructions to upgrade from version v8.1.0-dev to v9.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

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

### Tools

- apply coding standards checks on your `app` folder ([#1306](https://github.com/shopsys/shopsys/pull/1306))
    - add `app/router.php` to skipped files for two rules in your `easy-coding-standard.yml`:
        ```diff
          Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff:
              - '*/tests/ShopBundle/Functional/EntityExtension/EntityExtensionTest.php'
              - '*/tests/ShopBundle/Test/Codeception/_generated/AcceptanceTesterActions.php'
        +     - '*/app/router.php'

        + Shopsys\CodingStandards\Sniffs\ForbiddenSuperGlobalSniff:
        +     - '*/app/router.php'
        ```
  - run `php phing standards-fix` and fix possible violations that need to be fixed manually

[shopsys/framework]: https://github.com/shopsys/framework
