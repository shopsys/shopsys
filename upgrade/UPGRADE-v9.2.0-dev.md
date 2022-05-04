# [Upgrade from v9.1.0 to v9.2.0-dev](https://github.com/shopsys/shopsys/compare/v9.1.0...master)

This guide contains instructions to upgrade from version v9.1.0 to v9.2.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Composer dependencies
- remove `vasek-purchart/console-errors-bundle` dependency ([#2408](https://github.com/shopsys/shopsys/pull/2408))
    - see #project-base-diff to update your project
- upgrade all Doctrine related packages to the versions that support both PHP 7.4 and 8.1 ([#2417](https://github.com/shopsys/shopsys/pull/2417))
    - see #project-base-diff to update your project
    - make sure you check all the upgrading notes of the upgraded packages and adjust your codebase appropriately
        - see https://github.com/doctrine/dbal/blob/3.3.x/UPGRADE.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-1.11.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-1.12.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.0.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.1.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.2.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.3.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.4.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.5.md
        - see https://github.com/doctrine/persistence/blob/2.4.x/UPGRADE.md
        - see https://github.com/doctrine/orm/blob/2.11.x/UPGRADE.md
        - see https://github.com/trikoder/oauth2-bundle/blob/v3.x/UPGRADE.md
    - `DatabaseDumpCommand::__construct` changed its interface:
        ```
        - public function __construct(Connection $connection)
        + public function __construct(DatabaseConnectionCredentialsProvider $databaseConnectionCredentialsProvider)
        ```
    - `EntityExtensionSubscriber::getClassMetadataForEntity` changed its interface:
        ```diff
        - protected function getClassMetadataForEntity(string $entityClass): ClassMetadataInfo
        + protected function getClassMetadataForEntity(string $entityClass): ClassMetadata
        ```
    - `Admin/AdvertController` has a new dependency on `EntityManagerInterface` in the constructor
    - `Admin/BrandController` has a new dependency on `EntityManagerInterface` in the constructor
    - `Admin/SliderController` has a new dependency on `EntityManagerInterface` in the constructor
- allow installation of `jms/translation-bundle` version `1.6.2` and higher in `composer.json` ([#2420](https://github.com/shopsys/shopsys/pull/2420))
    - see #project-base-diff to update your project
- **\[BC break\]** upgrade `lcobucci/jwt` ([#2419](https://github.com/shopsys/shopsys/pull/2419))
    - see #project-base-diff to update your project
    - make sure you check upgrading notes of the upgraded package and adjust your codebase appropriately
        - https://lcobucci-jwt.readthedocs.io/en/latest/upgrading/
    - methods from following classes now accepts and returns `UnencryptedToken` instead of `Token`
        - `Shopsys\FrontendApiBundle\Model\Token\TokenFacade`
        - `Shopsys\FrontendApiBundle\Model\User\FrontendApiUserFactoryInterface`
        - `Shopsys\FrontendApiBundle\Model\User\FrontendApiUserFactory`
        - `Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider`
    - following methods were removed
        - `Shopsys\FrontendApiBundle\Model\Token\TokenFacade::getPublicKey()`, use `Lcobucci\JWT\Configuration::signingKey()` instead
        - `Shopsys\FrontendApiBundle\Model\Token\TokenFacade::getPrivateKey()`, use `Lcobucci\JWT\Configuration::verificationKey()` instead
        - `Shopsys\FrontendApiBundle\Model\Token\TokenFacade::getSigner()`, use `Lcobucci\JWT\Configuration::signer()` instead
        - `Lcobucci\JWT\Configuration` class is preconfigured as a service and can be injected with DI
    - generated access and refresh tokens now contain mandatory claim `nbf` (not before), 
      all your previously generated tokens need to be issued again, otherwise they will be rejected as expired
- upgrade `jms/metadata` and `jms/serializer-bundle` ([#2421](https://github.com/shopsys/shopsys/pull/2421))
    - see #project-base-diff to update your project
- upgrade dependencies related to PhpUnit ([#2424](https://github.com/shopsys/shopsys/pull/2424))
    - see #project-base-diff to update your project
    - new version of `zalas/phpunit-injector` requires typehint for injected properties
        - coding standard requiring typehints in tests can be enabled to help migration (see PR for details)

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
- **\[BC break\]** dropped support for unsupported PHP in packages ([#2416](https://github.com/shopsys/shopsys/pull/2416))
    - packages `shopsys/migrations`, `shopsys/coding-standards`, and `shopsys/http-smoke-testing` no longer support PHP 7.2 and 7.3
    - support for PHP 8.0 and 8.1 added to `shopsys/coding-standards` and `shopsys/http-smoke-testing`
- **\[BC break\]** upgrade `"doctrine/migrations` and `doctrine/doctrine-migrations-bundle` ([#2414](https://github.com/shopsys/shopsys/pull/2414))
    - see #project-base-diff to update your project
    - update your `migrations-lock.yml` to fit the new format
        - remove the "class" section
        - use the FQCN as a key
    - you need to update your `migrations` table structure
        - this will happen automatically during database migrations execution, or you can trigger it manually using `migrations:sync-metadata-storage` command
    - the `Shopsys\MigrationBundle\Component\Doctrine\Migrations\Configuration` class was removed
        - instead of `getMigrations()`, you can use new `MigrationLockPlanCalculator::getMigrations()`
        - instead of `getMigrationsToExecute()`, you can use new `MigrationLockPlanCalculator::getPlanUntilVersion()`
    - the `Shopsys\MigrationBundle\Command\AbstractCommand` class was removed
    - methods in `Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration` are now strictly typed
    - the `MigrationsLock` class changed:
        - the properties and methods are now strictly typed
        - `LoggerInterface` is now required as a second argument in the constructor
        - `saveNewMigrations` method now accepts `AvailableMigrationsList $availableMigrationsList` instead of `array $migrationVersions` as an argument
    - the `Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsFinder` and `MigrationsLocator` classes were removed
        - to register migrations of a bundle, you should leverage `PrependExtensionInterface`
    - methods and properties visibility of all classes in `shopsys/migrations` package changed from `private` to `protected` to ease possible extension
- shopsys/backend-api package was temporarily suspended as it was put under review to match our vision of the product ([#2422](https://github.com/shopsys/shopsys/pull/2422))
    - if you need the backend-api in the current state, you can fork it and handle the compatibility with the new versions of the other packages
- allow running npm scripts even when they are not executable ([#2403](https://github.com/shopsys/shopsys/pull/2403))
    - see #project-base-diff to update your project
- **\[BC break\]** class `\Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer` was changed ([#2426](https://github.com/shopsys/shopsys/pull/2426))
    - constructor changed interface
        ```diff
            /**
             * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap
        +    * @param \Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser $docBlockParser
             */
            public function __construct(
                AnnotationsReplacementsMap $annotationsReplacementsMap,
        +       DocBlockParser $docBlockParser
            ) {
        ```
- **\[BC break\]** class `\Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory` was changed
    - constructor changed interface
        ```diff
            /**
             * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap
             * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer $annotationsReplacer
        +    * @param \Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser $docBlockParser
             */
             public function __construct(
                 AnnotationsReplacementsMap $annotationsReplacementsMap,
                 AnnotationsReplacer $annotationsReplacer,
        +        DocBlockParser $docBlockParser
             ) {
        ```
- upgrade PHP version to 8.1 ([#2430](https://github.com/shopsys/shopsys/pull/2430))
    - see #project-base-diff to update your project
    - update your dependencies with `composer update` after you set `platform.php` in `composer.json` to the required version
    - if you use custom Dockerfile, don't forget to rebuild your image with the new version of PHP
- class `\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser` no longer implements `Serializable` interface
    - public methods `serialize()` and `unserialize()` were removed ([#2431](https://github.com/shopsys/shopsys/pull/2431))
    - `__serialize()` and `__serialize()` are used instead
- class `\Shopsys\FrameworkBundle\Model\Administrator\Administrator` no longer implements `Serializable` interface ([#2431](https://github.com/shopsys/shopsys/pull/2431))
    - public methods `serialize()` and `unserialize()` were removed
    - `__serialize()` and `__unserialize()` are used instead
- upgrade easy coding standards to v10 ([#2435](https://github.com/shopsys/shopsys/pull/2435))
    - see #project-base-diff to update your project
    - code style was adjusted, don't forget to check standards and update your code accordingly
- Doctrine Collections - use `getValues()` instead of `toArray()` ([#2439](https://github.com/shopsys/shopsys/pull/2439))
    - For details see issue ([#2409](https://github.com/shopsys/shopsys/issues/2409))
    ```diff
        $collection = new ArrayCollection([0, 1]);
    -   $collection->toArray();
    +   $collection->getValues();
    ```
