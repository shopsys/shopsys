# Upgrade Instructions for Interchangeable Filtering

This article describes the upgrade instructions for [#943 Elasticsearch filtering](https://github.com/shopsys/shopsys/pull/943).
Upgrade instructions are in a separate article because there is a lot of instructions and we don't want to jam the [UPGRADE-v7.2.0.md](UPGRADE-v7.2.0.md).
Instructions are meant to be followed when you upgrade from `v7.1.0` to `v7.2.0`.

In order to avoid a BC break and to ease upgrade to the new version of Shopsys Framework,
we decided to allow to use either the current (SQL) or the newly created (Elasticsearch) implementation of filtering.
Thanks to this, you can still easily upgrade to the new version without the need to rewrite your application
or use faster filtering with Elasticsearch we were able to deliver in the minor version as it does not break our BC promise.

You can learn more about [Product filtering](https://docs.shopsys.com/en/latest/model/front-end-product-filtering/) in the particular article.

## Use current SQL Filtering
You can still filter products via SQL if your filtering is more complex and/or you don't want to implement your custom modification in Elasticsearch.

Even then, you should make some upgrade steps to have your code tested properly and follow the recommendation for the project repository.

- replace occurrences of class `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade` with the interface `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface` in
    - `src/Shopsys/ShopBundle/Controller/Front/ProductController.php`
    - `src/Shopsys/ShopBundle/Controller/Front/SearchController.php`
- add a service definition for facade interface to your `services.yml` like
    ```yaml
    Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface: '@Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade'
    ```
- in order to have your code properly tested, copy the following [tests from shopsys/project-base](https://github.com/shopsys/project-base/blob/v7.2.0/tests/ShopBundle/Functional/Model/Product) into your project
    - `ProductOnCurrentDomainFacadeCountDataTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainFacadeCountDataTest.php))
    - `ProductOnCurrentDomainFacadeTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainFacadeTest.php)) -
    rewrite your current test class with this one, which is abstract to allow for reuse between the SQL and Elasticsearch filtering
    - `ProductOnCurrentDomainSqlFacadeTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainSqlFacadeTest.php))
    - `ProductOnCurrentDomainSqlFacadeCountDataTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainSqlFacadeCountDataTest.php))
    - `Filter/BrandFilterChoiceRepositoryTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/Filter/BrandFilterChoiceRepositoryTest.php))
    - `Filter/FlagFilterChoiceRepositoryTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/Filter/FlagFilterChoiceRepositoryTest.php))
    - `Filter/ParameterFilterChoiceRepositoryTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/Filter/ParameterFilterChoiceRepositoryTest.php))
    - `Search/FilterQueryTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/Search/FilterQueryTest.php), [view fixed in v7.2.1](https://github.com/shopsys/project-base/raw/v7.2.1/tests/ShopBundle/Functional/Model/Product/Search/FilterQueryTest.php))
    - be careful when overwriting your own test with a copy from `shopsys/project-base` so you don't lose any of your custom test-cases or modifications
- skip the path `'*/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainFacadeCountDataTest.php'` for following coding standard sniffs in your `easy-coding-standard.yml` file
    `ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff`
    `ObjectCalisthenics\Sniffs\Files\ClassTraitAndInterfaceLengthSniff`
- to ease your life in the future you should
    - replace the Elasticsearch structure files in the `src/Shopsys/ShopBundle/Resources/definition/products/` folder in your project with the new ones from [definitions in shopsys/project-base](https://github.com/shopsys/project-base/blob/v7.2.0/src/Shopsys/ShopBundle/Resources/definition/product/)
    - add the following alias to `services.yml` and `services_test.yml` to start exporting more data into Elasticsearch
        ```yaml
       Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportRepository: '@Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository'
        ```
    - update the test of the export repository `tests/ShopBundle/Functional/Model/Product/Search/ProductSearchExportRepositoryTest.php` to match the new Elasticsearch structure
        - you can copy [`ProductSearchExportRepositoryTest.php`](https://github.com/shopsys/project-base/blob/v7.2.0/tests/ShopBundle/Functional/Model/Product/Search/ProductSearchExportRepositoryTest.php) from `shopsys/project-base`
        - the test is written in such a way that supports both the SQL and Elasticsearch filtering (depending on the service in your DIC)
    - recreate the structure and export products to Elasticsearch with `php phing product-search-recreate-structure product-search-export-products` on your machine, and also later during deployment of your application

## Use new Elasticsearch Filtering
To start filtering products via Elasticsearch you have to do these steps.

- replace the Elasticsearch structure files in the `src/Shopsys/ShopBundle/Resources/definition/products/` folder in your project with the new ones from [definitions in `shopsys/project-base`](https://github.com/shopsys/project-base/blob/v7.2.0/src/Shopsys/ShopBundle/Resources/definition/product/)
- replace occurrences of class `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade` with the interface `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface` in
    - `src/Shopsys/ShopBundle/Controller/Front/ProductController.php`
    - `src/Shopsys/ShopBundle/Controller/Front/SearchController.php`
- add a service definition for facade interface to your `services.yml` like
    ```yaml
   Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface: '@Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade'
    ```
- add the following alias to `services.yml` and `services_test.yml` to start exporting more data into Elasticsearch
    ```yaml
   Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportRepository: '@Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository'
    ```
- in order to have your code properly tested, copy the following [tests from shopsys/project-base](https://github.com/shopsys/project-base/tree/v7.2.0/tests/ShopBundle/Functional/Model/Product) to your project
    - `ProductOnCurrentDomainElasticFacadeCountDataTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainElasticFacadeCountDataTest.php))
    - `ProductOnCurrentDomainElasticFacadeTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainElasticFacadeTest.php))
    - `ProductOnCurrentDomainFacadeCountDataTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainFacadeCountDataTest.php))
    - `ProductOnCurrentDomainFacadeTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainFacadeTest.php)) -
    rewrite your current test class with this one, which is abstract to allow for reuse between the SQL and Elasticsearch filtering
    - `Filter/BrandFilterChoiceRepositoryTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/Filter/BrandFilterChoiceRepositoryTest.php))
    - `Filter/FlagFilterChoiceRepositoryTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/Filter/FlagFilterChoiceRepositoryTest.php))
    - `Filter/ParameterFilterChoiceRepositoryTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/Filter/ParameterFilterChoiceRepositoryTest.php))
    - `Search/FilterQueryTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/Search/FilterQueryTest.php), [view fixed in v7.2.1](https://github.com/shopsys/project-base/raw/v7.2.1/tests/ShopBundle/Functional/Model/Product/Search/FilterQueryTest.php))
    - `Search/ProductSearchExportRepositoryTest.php` ([view raw](https://github.com/shopsys/project-base/raw/v7.2.0/tests/ShopBundle/Functional/Model/Product/Search/ProductSearchExportRepositoryTest.php)) -
    rewrite your current test class with this one, which is written in such a way that supports both the SQL and Elasticsearch filtering (depending on the service in your DIC)
    - be careful when overwriting your own test with a copy from `shopsys/project-base` so you don't lose any of your custom test-cases or modifications
- skip the path `'*/tests/ShopBundle/Functional/Model/Product/ProductOnCurrentDomainFacadeCountDataTest.php'` for the following coding standard sniffs in your `easy-coding-standard.yml` file
    `ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff`
    `ObjectCalisthenics\Sniffs\Files\ClassTraitAndInterfaceLengthSniff`
- don't forget to recreate the structure and export products to Elasticsearch with `php phing product-search-recreate-structure product-search-export-products`, especially later during deployment of your application
