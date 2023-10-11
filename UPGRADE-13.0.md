# UPGRADING FROM 12.x to 13.0
The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading
Since there are two possible scenarios how you can use the Shopsys Platform, instructions are divided into these scenarios.

### You use our packages only
Follow the instructions in relevant sections, e.g. `shopsys/coding-standards` or `shopsys/http-smoke-testing`.

### You are developing a project based on the project-base repository
* upgrade only your composer dependencies and follow the instructions in the guide below
* upgrade locally first. After you fix all issues caused by the upgrade, commit your changes, test your application and then continue with a deployment onto your server
* upgrade one version at a time:
    * start with a working application
    * upgrade to the next version
    * fix all the issues you encounter
    * repeat
* check the instructions in all sections, any of them could be relevant for you
* the typical upgrade sequence should be:
    * run `docker-compose down --volumes` to turn off your containers
    * *(macOS only)* run `mutagen-compose down --volumes` instead
    * follow upgrade notes in the *Infrastructure* section (related with `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    * run `docker-compose build --no-cache --pull` to build your images without a cache and with the latest version
    * run `docker-compose up -d --force-recreate --remove-orphans` to start the application again
    * update the `shopsys/*` dependencies in `composer.json` to version you are upgrading to
        * eg. `"shopsys/framework": "v7.0.0"`
    * follow upgrade notes in the *Composer dependencies* section (related with `composer.json`)
    * run `composer update shopsys/* --with-dependencies`
    * update the `@shopsys/framework` package in your `package.json` (in "dependencies" section) to the version you are upgrading to
        * eg. `"@shopsys/framework": "9.0.4",`
    * run `npm install` to update the NPM dependencies
    * follow all upgrade notes you have not done yet
    * run `php phing clean`
    * run `php phing db-migrations` to run the database migrations
    * test your app locally
    * commit your changes
    * run `composer update` to update the rest of your dependencies, test the app again and commit `composer.lock`
* if any of the database migrations does not suit you, there is an option to skip it, see [our Database Migrations docs](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)
* even if we care a lot about these instructions, it is possible we miss something. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG-12.1.md)

<!-- Insert upgrade instructions in the following format:
- general instruction ([#<PR number>](https://github.com/shopsys/shopsys/pull/<PR number>))
    - additional instructions
    - see #project-base-diff to update your project
-->

## [Upgrade from v12.0.0 to v13.0.0-dev](https://github.com/shopsys/shopsys/compare/v12.0.0...13.0)

The version 13.0.0 of the Shopsys Platform is a huge milestone in the history of the project.
It brings a lot of new features and improvements, but also some breaking changes.

The most significant change is the replacement of the Twig storefront with a JS Storefront built in Next.js.
This change is a major shift in the way the storefront operates and is customized.

## You have three options for upgrading to version 13.0.0:

1. **Keep the existing Twig storefront without incorporating new features**
   This is the simplest option, but you won't be able to take advantage of the new features introduced in version 13.0.0.
   You still get the fixes, and you will be ready to upgrade to the new storefront in the future.
2. **Keep the existing Twig storefront and add new features**
   This option allows you to benefit from the new features while keeping your existing storefront.
   However, you will need to implement these features on the storefront yourself.
3. **Reinvent the storefront and use new features**
   This is the most comprehensive option.
   You will replace your Twig storefront with the new JS Storefront powered by GraphQL.
   This will allow you to take full advantage of all the new features and improvements in version 13.0.0.

### Option 1: Keep the existing Twig storefront without incorporating new features

This is a quick and easy option that will allow you to upgrade to version 13.0.0 without major changes in your storefront.
You will be able to take advantage of the new features in the future when you decide.

- you will need to update the shopsys/* dependencies to version 13.0.0 and then follow the common steps for all upgrade options
    - it's possible that some DB migrations will be missing in the project-base, if so, you can add them manually following the output of the db-check Phing target

### Option 2: Keep the existing Twig storefront and add new features

This option allows you to benefit from the new features while keeping your existing storefront.

You will need to follow these steps:

- update the shopsys/* dependencies to version 13.0.0
- follow the common steps for all upgrade options
- examine the changes in the project-base and apply them to your project
    - this may be the most time-consuming part of the upgrade depending on the size of your project and the nature of custom changes
- for any new feature you want to use (improved Stocks, Stores, Opening hours, Blog articles, etc.) you will need to implement the functionality on your existing Twig Storefront

### Option 3: Reinvent the storefront and use new features

This is the most comprehensive option, but allows you to fully benefit from all the new features and improvements in version 13.0.0.

You will need to follow these steps:

- update the shopsys/* dependencies to version 13.0.0
- enable Frontend API
- follow the common steps for all upgrade options
- examine the changes in the project-base and apply them to your project  
- adjust the existing JS storefront to your needs
    - you can use the new JS storefront as a starting point for your customizations
    - you will need to adjust the design to match your needs
    - you will need to implement the customizations from your previous storefront implementation

## Common steps for all upgrade options

- split functional and frontend-api tests into separate suites ([#2641](https://github.com/shopsys/shopsys/pull/2641))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/f11b324b66fcca9e1c613510e222934ebb54314b) to update your project
- use TestCurrencyProvider from the framework ([#2662](https://github.com/shopsys/shopsys/pull/2662))
    - remove class `Tests\App\Functional\Model\Pricing\Currency\TestCurrencyProvider` and use `Tests\FrameworkBundle\Test\Provider\TestCurrencyProvider` instead
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/38507d7822084cbeccf070b4f70914a379f2de5b) to update your project
- fix S3Bridge bundle name ([#2648](https://github.com/shopsys/shopsys/pull/2648))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/e2853e73c2681440976eff2ad8b3633befbd9785) to update your project
- add opening hours to stores ([#2660](https://github.com/shopsys/shopsys/pull/2660))
    - `Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig`
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    $id,
                    $url,
                    $name,
                    $locale,
            +       DateTimeZone $dateTimeZone,
                    $stylesDirectory = self::STYLES_DIRECTORY_DEFAULT,
                    $stylesDirectory = self::STYLES_DIRECTORY_DEFAULT,
                    $designId = null,
                ) {
            ```
    - to start using opening hours of stores, set store opening hours in administration or create specialized migrations for it
        - after update add this to your migration: `$this->sql('ALTER TABLE stores DROP COLUMN opening_hours');`
    - add the new configuration option `timezone` to every domain in your `config/domains.yaml` and set it to the domain desired timezone
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/6f47acc7ee5047fae1554a0ffdb747ee36b24337) to update your project
- change url for listing personal detail from email ([#2725](https://github.com/shopsys/shopsys/pull/2725))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/d56ff558280e2876b74312a3c30e7a7c953e3658) to update your project
- fix product video UX ([#2746](https://github.com/shopsys/shopsys/pull/2746))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/4e938a87b714b9ec511c8454b6b4c6e33b134a4e) to update your project
- fix filtering in ReadyCategorySeoMix ([#2747](https://github.com/shopsys/shopsys/pull/2747))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/f4e8addd53049e8c78d4f4abc5f2e6d4888f7c0c) to update your project
- update your tests to be multilingual ([#2742](https://github.com/shopsys/shopsys/pull/2742))
    - add `ContainerAwareInterface` to migrations that use `MultidomainMigrationTrait`
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/cef0c2976f0fae060dab05f0d07383084027bc62) to update your project
- fix Node.js and PostgreSQL installation in php-fpm docker image ([#2758](https://github.com/shopsys/shopsys/pull/2758))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/a387db6dcfcde1eac7b43bc96d4411c6eb52adf6) to update your project
- use shopsys/php-image docker image instead of building it in the project ([#2762](https://github.com/shopsys/shopsys/pull/2762))
    - following files may be deleted as they are already present in the pre-built docker image
        - docker/php-fpm/docker-install-composer
        - docker/php-fpm/docker-php-entrypoint
        - docker/php-fpm/phing-completion
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/f3ada16c260fcd76325ec3a124cc2d6815de429c) to update your project
- remove usage of article placement ([#2776](https://github.com/shopsys/shopsys/pull/2776))
    - placement `Shopsys\FrameworkBundle\Model\Article\Article::PLACEMENT_TOP_MENU` was removed along with the usages
    - already existing articles with this placement will be migrated to `Shopsys\FrameworkBundle\Model\Article\Article::PLACEMENT_NONE` placement
        - if you intend to keep using this placement, you can skip DB Migration `Shopsys\FrameworkBundle\Migrations\Version20230907132822`
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/fdfd8da31a28b885523779355b6296dbfcf0c956) to update your project
- update your Elasticsearch Commands to support optional domainId ([#2780](https://github.com/shopsys/shopsys/pull/2780))
    - `\Shopsys\FrameworkBundle\Command\Elasticsearch\AbstractElasticsearchIndexCommand`
        - `executeForIndex()` changed its interface
        ```diff
            protected function executeForIndex(
                OutputInterface $output,
                AbstractIndex $index,
        +       ?int $domainId = null
            ): void
        ```
- fix and improve JS translations in administration ([#2779](https://github.com/shopsys/shopsys/pull/2779))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/84011e5e458813438ff822f8c816fe5c8a99ebdb) to update your project
- remove the misleading url addresses list from administration ([#2782](https://github.com/shopsys/shopsys/pull/2782))
    - `Shopsys\FrameworkBundle\Controller\Admin\SuperadminController`
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    protected readonly ModuleList $moduleList,
                    protected readonly ModuleFacade $moduleFacade,
                    protected readonly PricingSetting $pricingSetting,
                    protected readonly DelayedPricingSetting $delayedPricingSetting,
            -       protected readonly GridFactory $gridFactory,
                    protected readonly Localization $localization,
                    protected readonly LocalizedRouterFactory $localizedRouterFactory,
                    protected readonly MailSettingFacade $mailSettingFacade,
                    protected readonly MailerSettingProvider $mailerSettingProvider,
                    protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
                ) {
            ```
        - method `loadDataForUrls()` was removed
- Products – Exposed in Stores, Category - SVG icon, and Category - Short description have been removed ([#2777](https://github.com/shopsys/shopsys/pull/2777))
    - if you use this functionality (e.g., from Commerce Cloud version), you can skip DB migration 20230908095905
- adjust test for variant creation from products with images ([#2802](https://github.com/shopsys/shopsys/pull/2802))
    - edit Tests\App\Functional\Model\Product\ProductVariantCreationTest::testVariantWithImageCanBeCreated
        ```diff
            $mainVariant = $this->productVariantFacade->createVariant($mainProduct, $variants);

            $this->assertTrue($mainVariant->isMainVariant());
        -   $this->assertContainsAllVariants([$mainProduct, ...$variants], $mainVariant);
        +   $this->assertContainsAllVariants($variants, $mainVariant);
        ```
- rewrite the `GetOrdersAsAuthenticatedCustomerUserTest` test ([#2805](https://github.com/shopsys/shopsys/pull/2805))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/1f140de2ee0eacc82420d2c42127e3060e915ad1) to update your project
- change default db server in adminer in local environment ([#2803](https://github.com/shopsys/shopsys/pull/2803))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/c15b677336f491eb4de0878dce178f5c3387ca40) to update your project
- allow quick searching in promo codes ([#2786](https://github.com/shopsys/shopsys/pull/2786))
    - method `Shopsys\FrameworkBundle\Controller\Admin\PromoCodeController::listAction` changed its interface:
        ```diff
        -    public function listAction()
        +    public function listAction(Request $request): Response
        ```
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/689297fe111c506973d44e9f30b7b0b600f65446) to update your project
- stop wrapping Frontend API queries in database transaction ([#2809](https://github.com/shopsys/shopsys/pull/2809))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/bb60a071d139371b95601a6c678f485a5ffae0e8) to update your project
- add order filter by domain in admin ([#2796](https://github.com/shopsys/shopsys/pull/2796))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/a159284a66bc986faf7b872456923f98a8c5ae05) to update your project
    - see also [project-base-diff](https://github.com/shopsys/project-base/commit/178d8f9235393444cf259b93c66e96413d78d835) (#2844) for permission fix
    - `Shopsys\FrameworkBundle\Controller\Admin\CategoryController`
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    protected readonly CategoryFacade $categoryFacade,
                    protected readonly CategoryDataFactoryInterface $categoryDataFactory,
                    protected readonly Domain $domain,
                    protected readonly BreadcrumbOverrider $breadcrumbOverrider,
            -       protected readonly RequestStack $requestStack,
            +       protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
                ) {
            ```
        - constant `ALL_DOMAINS` was removed
        - method `listDomainTabsAction()` was removed
    - `Shopsys\FrameworkBundle\Controller\Admin\OrderController`
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    protected readonly OrderFacade $orderFacade,
                    protected readonly AdvancedSearchOrderFacade $advancedSearchOrderFacade,
                    protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
                    protected readonly AdministratorGridFacade $administratorGridFacade,
                    protected readonly GridFactory $gridFactory,
                    protected readonly BreadcrumbOverrider $breadcrumbOverrider,
                    protected readonly OrderItemFacade $orderItemFacade,
                    protected readonly Domain $domain,
                    protected readonly OrderDataFactoryInterface $orderDataFactory,
            +       protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
                ) {
            ```
    - `Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig`
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    OrderNumberFilter $orderNumberFilter,
                    OrderCreateDateFilter $orderCreateDateFilter,
                    OrderPriceFilterWithVatFilter $orderPriceFilterWithVatFilter,
            -       OrderDomainFilter $orderDomainFilter,
                    OrderStatusFilter $orderStatusFilter,
                    OrderProductFilter $orderProductFilter,
                    OrderPhoneNumberFilter $orderPhoneNumberFilter,
                    OrderStreetFilter $orderStreetFilter,
                    OrderNameFilter $orderNameFilter,
                    OrderLastNameFilter $orderLastNameFilter,
                    OrderEmailFilter $orderEmailFilter,
                    OrderCityFilter $orderCityFilter,
            -       Domain $domain,
                ) {
            ```
    - class `Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter\OrderDomainFilter` was removed
    - Twig template `@ShopsysFramework/Admin/Content/Category/domainTabs.html.twig` was removed
- prevent FileNotFoundException on a second flush of uploaded file ([#2655](https://github.com/shopsys/shopsys/pull/2655))
    - interface `Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface` has new method
        ```php
            public function setFileKeyAsUploaded(string $key): void;
        ```
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/63115e38a03a449afe981e69186dcff355f88f62) to update your project
- allow elasticsearch different index setting per environment for elasticsearch ([#2823](https://github.com/shopsys/shopsys/pull/2823))
    - new environment variable `FORCE_ELASTIC_LIMITS` may be used to force 1 shard and 0 replicas for elasticsearch indexes regardless of the settings in index definition JSON files
    - method `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition::__construct()` changed its interface
        ```diff
            public function __construct(
                protected readonly string $indexName,
                protected readonly string $definitionsDirectory,
                protected readonly string $indexPrefix,
                protected readonly int $domainId,
        +       protected readonly IndexDefinitionModifier $indexDefinitionModifier,
            ) {
        ```
    - method `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader::__construct()` changed its interface
        ```
            public function __construct(
                protected readonly string $indexDefinitionsDirectory,
                protected readonly string $indexPrefix,
        +       protected readonly IndexDefinitionModifier $indexDefinitionModifier,
            ) {
        ```
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/7e53c7640609421ed71ec0d1664f72c2c0db579b) to update your project
- update overblog settings to embrace composer autoloader for faster class loading ([#2830](https://github.com/shopsys/shopsys/pull/2830))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/b68cd77391e1b2b5e9b14b3e477377b543a7f27e) to update your project
- add ability to set readable frequency name to your cron ([#2854](https://github.com/shopsys/shopsys/pull/2854))
    - method `Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig::registerCronModuleInstance()` changed its interface
        ```diff
            public function registerCronModuleInstance(
                $service,
                string $serviceId,
                string $timeHours,
                string $timeMinutes,
                string $instanceName,
                ?string $readableName = null,
        +       ?string $readableFrequency = null,
                int $runEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
                int $timeoutIteratedCronSec = CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            ) {
        ```
    - method `Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig::__construct()` changed its interface
        ```diff
            public function __construct(
                protected readonly SimpleCronModuleInterface|IteratedCronModuleInterface $service,
                protected readonly string $serviceId,
                protected readonly string $timeHours,
                protected readonly string $timeMinutes,
                protected readonly ?string $readableName = null,
        +       protected readonly ?string $readableFrequency = null,
                protected readonly int $runEveryMin = self::RUN_EVERY_MIN_DEFAULT,
                protected readonly int $timeoutIteratedCronSec = self::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            ) {
        ```
- update LICENSE in your project as LICENSE has changed with this version ([#2849](https://github.com/shopsys/shopsys/pull/2849))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/516998f937e1fcd818056b8726d8f5b662f8115c) to update your project
