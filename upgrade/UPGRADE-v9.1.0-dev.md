# [Upgrade from v9.0.1 to v9.1.0-dev](https://github.com/shopsys/shopsys/compare/v9.0.1...master)

This guide contains instructions to upgrade from version v9.0.1 to v9.1.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application
- add `psr/event-dispatcher` to your composer dependencies in order to prevent PHPStan errors in Event classes ([#1894](https://github.com/shopsys/shopsys/pull/1894))
    - add `"psr/event-dispatcher": "^1.0.0",` to `require-dev` section in your `composer.json` file

- add support for changing personal data and password to your Frontend API ([#1891](https://github.com/shopsys/shopsys/pull/1891))
    - see #project-base-diff to update your project

- fix not working upload of files in wysiwyg editor ([#1899](https://github.com/shopsys/shopsys/pull/1899))
    - see #project-base-diff to update your project

- make Frontend API tests more reliable ([#1913](https://github.com/shopsys/shopsys/pull/1913))
    - see #project-base-diff to update your project

- update tests to use tests container to decrease amount of services defined in `services_test.yaml` ([#1957](https://github.com/shopsys/shopsys/pull/1957))
    - see #project-base-diff to update your project

- apply fixers for compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) ([#1324](https://github.com/shopsys/shopsys/pull/1324))
    - see #project-base-diff to update your project

- add new coding standards for YAML files ([#1552](https://github.com/shopsys/shopsys/pull/1552))
    - see #project-base-diff and update your `yaml-standards.yaml` file
    - run `php phing standards-fix` to fix your `yaml` files

- add phpstan-symfony extension ([#1961](https://github.com/shopsys/shopsys/pull/1961)) and ([#1974](https://github.com/shopsys/shopsys/pull/1974))
    - see #project-base-diff1 and #project-base-diff2 to update your project

- stop using Doctrine default value option ([#1395](https://github.com/shopsys/shopsys/pull/1395))
    - following properties no longer have a database default value, check if you set a default value in the constructor
        - `Shopsys\FrameworkBundle\Component\Cron\CronModule::$suspended`
        - `Shopsys\FrameworkBundle\Component\Cron\CronModule::$enabled`
        - `Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber::$createdAt`
        - `Shopsys\FrameworkBundle\Model\Product\Product::$recalculateAvailability`
        - `Shopsys\FrameworkBundle\Model\Product\Product::$recalculatePrice`
        - `Shopsys\FrameworkBundle\Model\Product\Product::$recalculateVisibility`

- introduced sniff for strict comparasion ([#1658](https://github.com/shopsys/shopsys/pull/1658))
    - this change is temporary to help you prepare your project for next major version, where this will be required directly in coding-standards package
    - see #project-base-diff to update your project

- moved setting of common entity data to new method ([#1976](https://github.com/shopsys/shopsys/pull/1976))
    - see #project-base-diff to update your project

- required new line at the end of each file ([#1989](https://github.com/shopsys/shopsys/pull/1989))
    - see #project-base-diff
    - to fix errors you may run `php phing standards-fix`

- phpstan analyse increased to level 5 ([#1922](https://github.com/shopsys/shopsys/pull/1922))
    - increasing phpstan level on your project is optional and may be set in `build.xml`
    - see #project-base-diff to update your project

- move npm-global directory to project in order to make it included in Docker volumes ([#2024](https://github.com/shopsys/shopsys/pull/2024))
    - see #project-base-diff to update your project

- update `docker/php-fpm/docker-php-entrypoint` to show all lines from first command output ([#1827](https://github.com/shopsys/shopsys/pull/1827))
    ```diff
        mkfifo $PIPE
        chmod 666 $PIPE
    -   tail -f $PIPE &
    +   tail -n +1 -f $PIPE &
    ```

- fix displaying '+' sign in product filter ([#2023](https://github.com/shopsys/shopsys/pull/2023))
    - see #project-base-diff to update your project

- remove setting domain locale in `CartFacadeTest` ([#2037](https://github.com/shopsys/shopsys/pull/2037))
    - in tests is used extended class of `Shopsys\FrameworkBundle\Model\Localization\TranslatableListener`
    - removed `config/packages/test/prezent_doctrine_translatable.yaml`
    - see #project-base-diff to update your project

- fix frontend-api tests when domain locales are changed ([#2019](https://github.com/shopsys/shopsys/pull/2019))
    - see #project-base-diff to update your project

- introduce read model layer into product detail ([#1999](https://github.com/shopsys/shopsys/pull/1999))
    - following methods has changed their interface:
        - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewElasticFacade::__construct()`
            ```diff
                public function __construct(
                    ProductFacade $productFacade,
                    ProductAccessoryFacade $productAccessoryFacade,
                    Domain $domain,
                    CurrentCustomerUser $currentCustomerUser,
                    TopProductFacade $topProductFacade,
                    ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
                    ListedProductViewFactory $listedProductViewFactory,
                    ProductActionViewFacade $productActionViewFacade,
            -       ImageViewFacade $imageViewFacade
            +       ImageViewFacadeInterface $imageViewFacade,
            +       ?ProductActionViewFactory $productActionViewFactory = null
                )
            ```
        - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory::__construct()`
            ```diff
                public function __construct(
                    Domain $domain,
            -       ProductCachedAttributesFacade $productCachedAttributesFacade
            +       ProductCachedAttributesFacade $productCachedAttributesFacade,
            +       ?ImageViewFacadeInterface $imageViewFacade = null,
            +       ?ProductActionViewFacadeInterface $productActionViewFacade = null
                )
            ```
    - following methods and properties were deprecated and will be removed in the next major version:
        - `Shopsys\ReadModelBundle\Image\ImageViewFacade::getForEntityIds()` use `getMainImagesByEntityIds()` instead
        - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewElasticFacade::$productActionViewFacade` 
            use `Shopsys\ReadModelBundle\Product\Listed\ProductActionViewFactory` instead
        - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewElasticFacade::createFromProducts()`
            use `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory::createFromProducts()` instead
        - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewElasticFacade::getIdsForProducts()`
            use `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory::getIdsForProducts()` instead
        - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacade::$imageViewFacade`
        - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacade::$productActionViewFacade`
        - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacade::createFromProducts()`
            use `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory::createFromProducts()` instead
        - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacade::getIdsForProducts()`
            use `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory::getIdsForProducts()` instead
    - see #project-base-diff to update your project

- added more coding standards ([#2035](https://github.com/shopsys/shopsys/pull/2035), [#2052](https://github.com/shopsys/shopsys/pull/2052))
    - the most of the rules have their own fixer, run `php phing ecs-fix` to resolve them
        - you need to run `ecs-fix` multiple times unless it is OK, because of the amount of changes
    - disallowed usage of `empty()` is one which must be fixed manually
        - first of all, you should keep in mind the `empty` ignores undefined offsets and is typed weak - this state should be probably kept
        - when your part of code has not been strictly typed yet, you should resolve it first otherwise you will be hacking a new `empty` function by yourself and this rule will have no benefit for you
        - if `empty` is used for checking elements of an array it can be replaced with `count($array)`

- remove unnecessary default values for ENV variables ([#2040](https://github.com/shopsys/shopsys/pull/2040))
    - these parameters should be configured via ENV variables if set
    - see #project-base-diff to update your project

- add brands in frontend API ([#2047](https://github.com/shopsys/shopsys/pull/2047))
    - see #project-base-diff to update your project

- add autocompletion for Phing targets ([#2049](https://github.com/shopsys/shopsys/pull/2049))
    - see #project-base-diff to update your project

- made tests to be domain independent ([#2051](https://github.com/shopsys/shopsys/pull/2051))
    - see #project-base-diff to update your project

- add additional data to Product frontend API type ([#2057](https://github.com/shopsys/shopsys/pull/2057))
    - see #project-base-diff to update your project

- made parameters overridable by ENV variables ([#2055](https://github.com/shopsys/shopsys/pull/2055))
    - how to configure your application with ENV variables can be found [in our docs](https://docs.shopsys.com/en/latest/installation/application-configuration/)
    - see #project-base-diff to update your project

- added Product SEO informations to elasticsearch ([#2074](https://github.com/shopsys/shopsys/pull/2074))
    - see #project-base-diff to update your project
    - run `php phing elasticsearch-index-migrate elasticsearch-export` to apply changes

- added SEO informations to frontend API ([#2067](https://github.com/shopsys/shopsys/pull/2067), [#2112](https://github.com/shopsys/shopsys/pull/2112))
    - see #project-base-diff to update your project

- add promoted products in frontend API ([#2066](https://github.com/shopsys/shopsys/pull/2066))
    - translation in the administration was changed from "Main page products" to "Promoted products"
        - translation ID will be changed accordingly in the next major
    - see #project-base-diff to update your project

- made frontend API tests to be currency independent ([#2075](https://github.com/shopsys/shopsys/pull/2075))
    - see #project-base-diff to update your project

- add adverts in frontend API ([#2068](https://github.com/shopsys/shopsys/pull/2068))
    - see #project-base-diff to update your project

- remove hirak/prestissimo from Dockerfile ([#2089](https://github.com/shopsys/shopsys/pull/2089))
    - make sure you have composer 2 installed (`composer --version`)
    - see #project-base-diff to update your project 

- fixed standards on new release of FriendsOfPHP/PHP-CS-Fixer ([#2094](https://github.com/shopsys/shopsys/pull/2094))
    - run `php phing standards-fix` to apply fixes

- load javascripts after content is loaded ([#1879](https://github.com/shopsys/shopsys/pull/1879))
    - if you set the parameter `fp_js_form_validator.twig_extension.class` to a custom class, please adjust this class according to [#1879](https://github.com/shopsys/shopsys/pull/1879)
    - see #project-base-diff to update your project

- allow placing scripts in administration after content([#2086](https://github.com/shopsys/shopsys/pull/2086))
    - see #project-base-diff to update your project

- increase reliability and decrease maintainability of acceptance tests ([#2099](https://github.com/shopsys/shopsys/pull/2099))
    - see #project-base-diff to update your project

- drop support for lower PHP versions than 7.4.1 ([#2109](https://github.com/shopsys/shopsys/pull/2109))
    - see #project-base-diff to update your project 
    - update your dependencies with `composer update` after you set `platform.php` in `composer.json` to the required version

- add support for customer user registration to your Frontend API ([#2100](https://github.com/shopsys/shopsys/pull/2100))
    - see #project-base-diff to update your project
    
- add support for ordering products to your Frontend API ([#2110](https://github.com/shopsys/shopsys/pull/2110))
    - see #project-base-diff to update your project

- ProductDetailView provided by elasticsearch ([#2090](https://github.com/shopsys/shopsys/pull/2090))
    - add new fields to elasticsearch index definition for all domains 
        - see #project-base-diff to update your project
    - run `php phing elasticsearch-index-migrate elasticsearch-export` to apply changes
        - with products there are exported variants too now
            - it should not cause any trouble as filtering product from Elasticsearch was edited to filter variants out, so it behaves same as earlier
            - when you have some of related functionality extended you should probably want to filter variants out by yourself
                - method `FilterQuery::filterOutVariants()` was introduced for this purposes

- fix cleaning of old redis cache ([#2096](https://github.com/shopsys/shopsys/pull/2096))
    - see #project-base-diff to update your project

- enable logging in tests ([#2113](https://github.com/shopsys/shopsys/pull/2113))
    - see #project-base-diff to update your project

- avoid missing or delayed logs ([#2103](https://github.com/shopsys/shopsys/pull/2103))
    - see #project-base-diff to update your project

- disable javascript validation for product filter form ([#2104](https://github.com/shopsys/shopsys/pull/2104))
    - see #project-base-diff to update your project

- move doctrine metadata and annotations cache to file instead of redis ([#2107](https://github.com/shopsys/shopsys/pull/2107))
    - class `Shopsys\FrameworkBundle\Component\Doctrine\Cache\FallbackCacheFactory` is deprecated and will be removed in next major version
        - in case you need it in your project you should implement it by yourself
    - see #project-base-diff to update your project
