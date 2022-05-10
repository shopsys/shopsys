# [Upgrade from v9.1.2 to v10.0.0-dev](https://github.com/shopsys/shopsys/compare/v9.1.2...master)

This guide contains instructions to upgrade from version v9.1.2 to v10.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Packages

- `shopsys/backend-api` package was temporarily suspended as it was put under review to match our vision of the product ([#2422](https://github.com/shopsys/shopsys/pull/2422))
    - if you need the backend-api in the current state, you can fork it and handle the compatibility with the new versions of the other packages
- dropped support for unsupported PHP in some packages ([#2416](https://github.com/shopsys/shopsys/pull/2416))
    - packages `shopsys/migrations`, `shopsys/coding-standards`, and `shopsys/http-smoke-testing` no longer support PHP 7.2 and 7.3

## Upgrade composer dependencies and PHP version

The main highlight of this version is upgrade of libraries and switch to PHP 8.1.
The first you should do is update your composer.json to have installable set on PHP 8.1 compatible with the new version of Shopsys Framework.
Pay special attention to any of your custom libraries as it's possible you will have to upgrade them too.  
All the changes are considered as backwards incompatible.

<!--- TODO change master to released version in the link  --->
- upgrade PHP version to 8.1 ([#2430](https://github.com/shopsys/shopsys/pull/2430))
    - update `docker/php-fpm/Dockerfile` and `scripts/install-docker-wsl-debian.sh` according to [project-base-diff](https://github.com/shopsys/project-base/commit/8ddaab98a3d3392c13735541505202cb63dfa5ce)
        <!--- TODO regenerate the gist during release  --->
    - apply all [composer.json changes](https://gist.github.com/ShopsysBot/85fd56266c93a75b575c24499463f0d9/revisions?diff=split) from this version at once
    - update your dependencies with `composer update` after you set `platform.php` in `composer.json` to the required version
    - if you use custom Dockerfile, don't forget to rebuild your image with the new version of PHP

- remove `vasek-purchart/console-errors-bundle` dependency ([#2408](https://github.com/shopsys/shopsys/pull/2408))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/431a89328e0a5cf91a4ada7ace3fa4ae60bb368b) to update your project
- upgrade all Doctrine related packages to the versions that support PHP 8.1 ([#2417](https://github.com/shopsys/shopsys/pull/2417))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/71bd88aa750ed4f7a21fd8ec6454f7bfe3008bd4) to update your project
    - make sure you check all the upgrading notes of the upgraded packages and adjust your codebase appropriately
        - see https://github.com/doctrine/dbal/blob/3.3.x/UPGRADE.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.0.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.1.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.2.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.3.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.4.md
        - see https://github.com/doctrine/DoctrineBundle/blob/2.5.x/UPGRADE-2.5.md
        - see https://github.com/doctrine/persistence/blob/2.4.x/UPGRADE.md
        - see https://github.com/doctrine/orm/blob/2.11.x/UPGRADE.md
    - `DatabaseDumpCommand::__construct` changed its interface:
        ```
        - public function __construct(Connection $connection)
        + public function __construct(DatabaseConnectionCredentialsProvider $databaseConnectionCredentialsProvider)
        ```
    - `Admin/AdvertController` has a new dependency on `EntityManagerInterface` in the constructor
    - `Admin/BrandController` has a new dependency on `EntityManagerInterface` in the constructor
    - `Admin/SliderController` has a new dependency on `EntityManagerInterface` in the constructor
- allow installation of `jms/translation-bundle` version `1.6.2` and higher in `composer.json` ([#2420](https://github.com/shopsys/shopsys/pull/2420))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/37f6b9108658a8dbca9e7aff9f417d9002ace358) to update your project
- replace dependency `fzaninotto/Faker` with `FakerPHP/Faker` ([#2413](https://github.com/shopsys/shopsys/pull/2413))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/b20b82215bf8a58a78a366b6857c5b2533537377) to update your project
- upgrade `lcobucci/jwt` ([#2419](https://github.com/shopsys/shopsys/pull/2419))
    - make sure you check upgrading notes of the upgraded package and adjust your codebase appropriately
        - https://lcobucci-jwt.readthedocs.io/en/latest/upgrading/
    - methods from following classes now accepts and returns `UnencryptedToken` instead of `Token`
        - `Shopsys\FrontendApiBundle\Model\Token\TokenFacade`
        - `Shopsys\FrontendApiBundle\Model\User\FrontendApiUserFactoryInterface`
        - `Shopsys\FrontendApiBundle\Model\User\FrontendApiUserFactory`
        - `Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider`
    - following methods were removed
        - `Shopsys\FrontendApiBundle\Model\Token\TokenFacade::getPublicKey()`, use `Lcobucci\JWT\Configuration::verificationKey()` instead
        - `Shopsys\FrontendApiBundle\Model\Token\TokenFacade::getPrivateKey()`, use `Lcobucci\JWT\Configuration::signingKey()` instead
        - `Shopsys\FrontendApiBundle\Model\Token\TokenFacade::getSigner()`, use `Lcobucci\JWT\Configuration::signer()` instead
        - `Lcobucci\JWT\Configuration` class is preconfigured as a service and can be injected with DI
    - generated access and refresh tokens now contain mandatory claim `nbf` (not before),
      all your previously generated tokens need to be issued again, otherwise they will be rejected as expired
- upgrade `jms/metadata` and `jms/serializer-bundle` ([#2421](https://github.com/shopsys/shopsys/pull/2421))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/45df441c787e7976abc2420e4257e008171e9fcc) to update your project
- change entity extension subscriber class ([#2405](https://github.com/shopsys/shopsys/pull/2405))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/976e2c9cf4e6e584a1b17869a6ac7f6e6c735935) to update your project
    - package `joschi127/doctrine-entity-override-bundle` is no longer used
    - previously used subscriber `\Joschi127\DoctrineEntityOverrideBundle\EventListener\LoadORMMetadataSubscriber` was replaced with `\Shopsys\FrameworkBundle\Component\EntityExtension\EntityExtensionSubscriber`
        - if you have extended `LoadORMMetadataSubscriber`, you will need to extend `EntityExtensionSubscriber` instead and reimplement your changes on top of the new class
- replace deprecated namespace `Doctrine\Common\Persistence` with new `Doctrine\Persistence` ([#2407](https://github.com/shopsys/shopsys/pull/2407))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/c1cd72051c5410f6cbe2885815f2eeb93a44b7cc) to update your project
- change used caches from `doctrine/cache` to Symfony cache ([#2412](https://github.com/shopsys/shopsys/pull/2412))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/c7dd971194ea721b9a38ed7d1e3df1a9b6eba1a4) to update your project
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
        - constant `CachedBestsellingProductFacade::LIFETIME` was removed and lifetime of cache was moved to cache pool configuration (see `bestselling_product_cache` in `config/packages/cache.yaml`)
        - protected property `$cacheProvider` was removed, use `$cache` instead
        - protected method `saveToCache` was removed, storing to cache is handled automatically in `getAllOfferedBestsellingProducts` method
    - following service definitions were removed in favor of cache pool configurations in `config/packages/cache.yaml`
        - `Doctrine\Common\Cache\ChainCache`
        - `shopsys.doctrine.cache_driver.query_cache`
        - `shopsys.doctrine.cache_driver.metadata_cache`
        - `shopsys.framework.cache_driver.annotations_cache`
        - `bestselling_product_cache`
    - if you have your own cache implemented with `Doctrine\Common\Cache` you may look at the [upgrade cache example](./upgrade-instructions-for-caches.md)
- upgrade `doctrine/migrations` and `doctrine/doctrine-migrations-bundle` ([#2414](https://github.com/shopsys/shopsys/pull/2414))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/3d113d5175e56222d52f90e183a904f1dca95e37) to update your project
    - update your `migrations-lock.yml` to fit the new format
        - remove the "class" section
        - use the FQCN as a key
    - **you need to update your `migrations` table structure**
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
- update coding standards configuration ([#2415](https://github.com/shopsys/shopsys/pull/2415), [#2435](https://github.com/shopsys/shopsys/pull/2435))
    - version 10.2.2 of `symplify/easy-coding-standard` is now required
    - removed dependency on `symplify/easy-coding-standard-tester`, you should change your custom coding standards tests
        - you can find inspiration in https://github.com/shopsys/shopsys/pull/2415/files
    - switch configuration of easy-coding-standard from yaml file to php file
        - if you use default Shopsys Framework configuration, you can just use default [`ecs.php`](https://github.com/shopsys/project-base/blob/master/ecs.php)
        - for your custom configuration, you can leverage https://github.com/symplify/config-transformer
    - see [project-base-diff-1](https://github.com/shopsys/project-base/commit/ddf72564195a625a288a4b8ee48559eded3bac58) and [project-base-diff-2](https://github.com/shopsys/project-base/commit/e45f75397880b124ab5fe32498126dfc536fb7d2) to update your project
    - code style was adjusted, don't forget to check standards and update your code accordingly
- upgrade dependencies related to PhpUnit ([#2424](https://github.com/shopsys/shopsys/pull/2424))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/4fa4954330e76dff6aaf420fd2db09dbb064ab17) to update your project
    - new version of `zalas/phpunit-injector` requires typehint for injected properties
        - coding standard requiring typehints in tests can be enabled to help migration (see PR for details)
- minor fixes in annotation replacer tool ([#2426](https://github.com/shopsys/shopsys/pull/2426))
    - check your custom changes in the tool
    - constructor `\Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer::__construct()` was changed
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
    - constructor `\Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory::__construct()` was changed
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

## Application

- fix memory_limit set for PHPUnit ([#2398](https://github.com/shopsys/shopsys/pull/2398))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/b81d7265d35f3f2dbf22b6041e8bdbf430a91254) to update your project
- update your composer dependencies ([#2397](https://github.com/shopsys/shopsys/pull/2397))
    - upgrade `codeception/codeception` to `^4.1.22` so you get rid of [the security problem](https://github.com/advisories/GHSA-4574-qv3w-fcmg)
        - you might need to update your `Tests\App\Test\Codeception\AcceptanceTester` to respect changes (added strict typehints) in `Tests\FrameworkBundle\Test\Codeception\ActorInterface`
        - in `StrictWebDriver::seeInElement`, use `assertStringContainsString` instead of `assertContains`
        - beware, the entry in `phpstan.neon` was fixed in the follow-up pull request ([#2404](https://github.com/shopsys/shopsys/pull/2404))
            - see [project-base-diff](https://github.com/shopsys/project-base/commit/739c970e33a3b135f17b3bfb86d013f1e0f389f4)
    - allow plugins in your `composer.json`
        - this is required when using composer in version `2.2.0` and above. If you are running your project in docker, you might need to re-build your docker image to get the upgraded composer
    - upgrade `composer/composer` to `^1.10.23` so you get rid of [the security problem](https://github.com/composer/composer/security/advisories/GHSA-frqg-7g38-6gcf)
    - extract parts of `CustomerUserDataFixture::getCustomerUserUpdateData()` into private methods to lower the cyclomatic complexity of the method
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/8badeded458456449f3996eaf81d0ea7074dc7bb) to update your project
- extract part of `install.sh` script into a new separate file (`configure.sh`) ([#2404](https://github.com/shopsys/shopsys/pull/2404))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/739c970e33a3b135f17b3bfb86d013f1e0f389f4) to update your project
- class `Shopsys\FrameworkBundle\Component\Csv\CsvReader` is deprecated, use `SplFileObject::fgetcsv()` instead ([#2218](https://github.com/shopsys/shopsys/pull/2218))
- use different css classes for javascript and tests ([#2179](https://github.com/shopsys/shopsys/pull/2179))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/8f515e883044c296f0aa969bd62b01aa1d27ea59) to update your project
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
    - you can use regular expression https://regex101.com/r/R1hWWQ/1 for quick replacement
    - for more information read our article [Icon function](https://docs.shopsys.com/en/latest/frontend/icon-function/)
- Doctrine Collections - use `getValues()` instead of `toArray()` ([#2439](https://github.com/shopsys/shopsys/pull/2439))
    - for details see issue ([#2409](https://github.com/shopsys/shopsys/issues/2409))
    ```diff
        $collection = new ArrayCollection([0, 1]);
    -   $collection->toArray();
    +   $collection->getValues();
    ```
- class `\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser` no longer implements `Serializable` interface ([#2431](https://github.com/shopsys/shopsys/pull/2431))
    - public methods `serialize()` and `unserialize()` were removed
    - `__serialize()` and `__serialize()` are used instead
- class `\Shopsys\FrameworkBundle\Model\Administrator\Administrator` no longer implements `Serializable` interface ([#2431](https://github.com/shopsys/shopsys/pull/2431))
    - public methods `serialize()` and `unserialize()` were removed
    - `__serialize()` and `__unserialize()` are used instead
- allow running npm scripts even when they are not executable ([#2403](https://github.com/shopsys/shopsys/pull/2403))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/76cfa5d35b11689ffdd126e3ca2079acbefb659f) to update your project
