# [Upgrade from v9.1.0 to v9.2.0-dev](https://github.com/shopsys/shopsys/compare/v9.1.0...master)

This guide contains instructions to upgrade from version v9.1.0 to v9.2.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Composer dependencies
- remove `vasek-purchart/console-errors-bundle` dependency ([#2408](https://github.com/shopsys/shopsys/pull/2408))
    - see #project-base-diff to update your project

## Application
- use different css classes for javascript and tests ([#2179](https://github.com/shopsys/shopsys/pull/2179))
    - see #project-base-diff to update your project

- class `Shopsys\FrameworkBundle\Component\Csv\CsvReader` is deprecated, use `SplFileObject::fgetcsv()` instead ([#2218](https://github.com/shopsys/shopsys/pull/2218))

- replace html icon tags with new twig tag icon ([#2274](https://github.com/shopsys/shopsys/pull/2274))
    - search for all occurrences of
        ```twig
        <i class="svg svg-<svg-icon-name>"></i>
        ```
        and replace it by
        ```twig
        {{ icon('<svg-icon-name>') }}
        ```
        Full example:
        ```diff
        -   <i class="svg svg-question"></i>
        +   {{ icon('question') }}
        ```
    - for more information read our article [Icon function](https://docs.shopsys.com/en/9.1/frontend/icon-function/)

- **\[BC break\]** change entity extension subscriber class ([#2405](https://github.com/shopsys/shopsys/pull/2405))
    - see #project-base-diff to update your project 
    - package joschi127/doctrine-entity-override-bundle is no longer used
    - previously used subscriber `\Joschi127\DoctrineEntityOverrideBundle\EventListener\LoadORMMetadataSubscriber` was replaced with `\Shopsys\FrameworkBundle\Component\EntityExtension\EntityExtensionSubscriber`
        - if you have extended `LoadORMMetadataSubscriber`, you will need to extend `EntityExtensionSubscriber` instead and reimplement your changes on top of the new class

- replace deprecated namespace `Doctrine\Common\Persistence\ObjectManager` with new `Doctrine\Persistence\ObjectManager` ([#2407](https://github.com/shopsys/shopsys/pull/2407))
    - see #project-base-diff to update your project

- replace dependency `fzaninotto/Faker` with `FakerPHP/Faker` ([#2413](https://github.com/shopsys/shopsys/pull/2413))
    - see #project-base-diff to update your project

- **\[BC break\]** change used caches from doctrine/cache to Symfony cache ([#2412](https://github.com/shopsys/shopsys/pull/2412))
    - see #project-base-diff to update your project
    - class `Shopsys\FrameworkBundle\DependencyInjection\Compiler\LazyRedisCompilerPass` was removed and the compiler pass is no longer registered
    - class `Shopsys\FrameworkBundle\Component\Doctrine\Cache\FallbackCacheFactory` was removed along with its service definition
    - class `Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade` was changed
        - constructor changed interface
            ```diff
                /**
            -    * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
            +    * @param \Symfony\Contracts\Cache\CacheInterface $cache
                 * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade $bestsellingProductFacade
                 * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
                 * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
                 */
                public function __construct(
            -       CacheProvider $cacheProvider,
            +       CacheInterface $cache,
                    BestsellingProductFacade $bestsellingProductFacade,
                    ProductRepository $productRepository,
                    PricingGroupRepository $pricingGroupRepository
                ) {
            ```
        - constant `LIFETIME` was removed and lifetime of cache was moved to cache pool configuration (see `bestselling_product_cache` in `config/packages/cache.yaml`)
        - protected property `$cacheProvider` was removed, use `$cache` instead
        - protected method `saveToCache` was removed, storing to cache is handled automatically in `getAllOfferedBestsellingProducts` method
    - following service definitions were removed in favor of cache pool configurations in `config/packages/cache.yaml`
        - `Doctrine\Common\Cache\ChainCache`
        - `shopsys.doctrine.cache_driver.query_cache`
        - `shopsys.doctrine.cache_driver.metadata_cache`
        - `shopsys.framework.cache_driver.annotations_cache`
        - `bestselling_product_cache`
- update coding standards configuration ([#2415](https://github.com/shopsys/shopsys/pull/2415))
    - version 8.3.48 of `symplify/easy-coding-standard` is now required
    - removed dependency on `symplify/easy-coding-standard-tester`, you should change your custom coding standards tests
        - you can find inspiration in https://github.com/shopsys/shopsys/pull/2415/files
    - switch configuration of easy-coding-standard from yaml file to php file
        - if you use default Shopsys Framework configuration, you can just use `ecs.php` (see #project-base-diff)
        - for your custom configuration, you can leverage https://github.com/symplify/config-transformer
    - see #project-base-diff to update your project 
