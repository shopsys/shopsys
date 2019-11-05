# [Upgrade from v8.1.0-dev to v9.0.0-dev](https://github.com/shopsys/shopsys/compare/HEAD...9.0)

This guide contains instructions to upgrade from version v8.1.0-dev to v9.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Infrastructure
- update your `kubernetes/deployments/webserver-php-fpm.yml` file: ([#1368](https://github.com/shopsys/shopsys/pull/1368))
    ```diff
    -   command: ["sh", "-c", "cd /var/www/html && ./phing db-create dirs-create db-demo product-search-recreate-structure product-search-export-products grunt error-pages-generate warmup"]
    +   command: ["sh", "-c", "cd /var/www/html && ./phing -D production.confirm.action=y db-create dirs-create db-demo product-search-recreate-structure product-search-export-products grunt error-pages-generate warmup"]
    ```
- check all the phing targets that depend on the new `production-protection` target
    - if you use any of the targets in your automated build scripts in production environment, you need to pass the confirmation to the phing using `-D production.confirm.action=y`

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
- add [`app/getEnvironment.php`](https://github.com/shopsys/shopsys/blob/9.0/project-base/app/getEnvironment.php) file to your project ([#1368](https://github.com/shopsys/shopsys/pull/1368))
- add optional [Frontend API](https://github.com/shopsys/shopsys/blob/9.0/docs/frontend-api/introduction-to-frontend-api.md) to your project ([#1445](https://github.com/shopsys/shopsys/pull/1445)):
    - add `shopsys/frontend-api` dependency with `composer require shopsys/frontend-api`
    - register necessary bundles in `app/AppKernel.php`
        ```diff
          new JMS\TranslationBundle\JMSTranslationBundle(),
          new Knp\Bundle\MenuBundle\KnpMenuBundle(),
        + new Shopsys\FrontendApiBundle\ShopsysFrontendApiBundle(),
        + new Overblog\GraphQLBundle\OverblogGraphQLBundle(),
          new Presta\SitemapBundle\PrestaSitemapBundle(),
        ```
        ```diff
          if ($this->getEnvironment() === EnvironmentType::DEVELOPMENT) {
        +     $bundles[] = new Overblog\GraphiQLBundle\OverblogGraphiQLBundle();
        ```
    - add new route resource at the end of `app/config/routing.yml`
        ```diff
        + shopsys_frontend_api:
        +     resource: "@ShopsysFrontendApiBundle/Resources/config/routing.yml"
        +     prefix: /graphql
        ```
    - add new route resource at the end of `app/config/routing_dev.yml`
        ```diff
        + shopsys_frontend_api:
        +     resource: "@ShopsysFrontendApiBundle/Resources/config/routing_dev.yml"
        +     prefix: /graphql
        ```
    - copy necessary type definitions
        [Query.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/Query.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/Query.types.yml`
        [Category.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/Category.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/Category.types.yml`
- add optional frontend API - products to your project ([#1471](https://github.com/shopsys/shopsys/pull/1471)):
    - copy necessary type definitions:
        [Availability.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/Availability.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/Availability.types.yml`
        [Flag.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/Flag.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/Flag.types.yml`
        [Money.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/Money.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/Money.types.yml`
        [Price.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/Price.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/Price.types.yml`
        [Product.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/Product.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/Product.types.yml`
        [Unit.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/Unit.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/Unit.types.yml`
    - copy necessary configuration [shopsys_frontend_api.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/app/config/packages/shopsys_frontend_api.yml) to `app/config/packages/shopsys_frontend_api.yml`
    - copy [tests for FrontendApiBundle from Github](https://github.com/shopsys/shopsys/tree/9.0/project-base/tests/FrontendApiBundle) to your `tests` folder
    - enable Frontend API for desired domains in `app/config/parameters_common.yml` file  
    for example
        ```diff
          parmeters:
              # ...
        +     shopsys.frontend_api.domains:
        +         - 1
        +         - 2
    - update your [`phpstan.neon`](https://github.com/shopsys/shopsys/blob/9.0/project-base/phpstan.neon): ([#1471](https://github.com/shopsys/shopsys/pull/1471))
      ```diff
      ignoreErrors:
          +    # In tests, we often grab services using $container->get() or access persistent references using $this->getReference()
          +    message: '#^Property (Shopsys|Tests)\\.+::\$.+ \(.+\) does not accept (object|object\|null)\.$#'
          +    path: %currentWorkingDirectory%/tests/FrontendApiBundle/*
      ```
- removed unused `block domain` defined in `Admin/Content/Slider/edit.html.twig` ([#1437](https://github.com/shopsys/shopsys/pull/1437)) 
    - in case you are using this block of code you should copy it into your project (see PR mentioned above for more details)

- add optional frontend API - products variant to your project ([#1489](https://github.com/shopsys/shopsys/pull/1489)):
    - copy necessary type definitions:
        [MainVariant.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/MainVariant.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/MainVariant.types.yml`
        [RegularProduct.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/RegularProduct.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/RegularProduct.types.yml`
        [Variant.types.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Shopsys/ShopBundle/Resources/graphql-types/Variant.types.yml) to `src/Shopsys/ShopBundle/Resources/graphql-types/Variant.types.yml`

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
