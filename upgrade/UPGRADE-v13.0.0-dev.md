# [Upgrade from v12.0.0 to v13.0.0-dev](https://github.com/shopsys/shopsys/compare/v12.0.0...13.0)

This guide contains instructions to upgrade from version v12.0.0 to v13.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/13.0/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- split functional and frontend-api tests into separate suites ([#2641](https://github.com/shopsys/shopsys/pull/2641))
    - see #project-base-diff to update your project
- use TestCurrencyProvider from the framework ([#2662](https://github.com/shopsys/shopsys/pull/2662))
    - remove class `Tests\App\Functional\Model\Pricing\Currency\TestCurrencyProvider` and use `Tests\FrameworkBundle\Test\Provider\TestCurrencyProvider` instead
    - see #project-base-diff to update your project
- fix S3Bridge bundle name ([#2648](https://github.com/shopsys/shopsys/pull/2648))
    - see #project-base-diff to update your project
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
    - to start using opening hours of stores set store opening hours in administration or create specialized migrations for it
        - after update add this to your migration: `$this->sql('ALTER TABLE stores DROP COLUMN opening_hours');`
- change url for listing personal detail from email ([#2725](https://github.com/shopsys/shopsys/pull/2725))
    - see #project-base-diff to update your project
- fix product video UX ([#2746](https://github.com/shopsys/shopsys/pull/2746))
    - see #project-base-diff to update your project
- fix filtering in ReadyCategorySeoMix ([#2747](https://github.com/shopsys/shopsys/pull/2747))
    - see #project-base-diff to update your project
- update your tests to be multilingual ([#2742](https://github.com/shopsys/shopsys/pull/2742))
    - add `ContainerAwareInterface` to migrations that use `MultidomainMigrationTrait`
    - see #project-base-diff to update your project
- fix NodeJS and PostgreSQL installation in php-fpm docker image ([#2758](https://github.com/shopsys/shopsys/pull/2758))
    - see #project-base-diff to update your project
- use shopsys/php-image docker image instead of building it in project ([#2762](https://github.com/shopsys/shopsys/pull/2762))
    - following files may be deleted as they are already present in the pre-built docker image
        - docker/php-fpm/docker-install-composer
        - docker/php-fpm/docker-php-entrypoint
        - docker/php-fpm/phing-completion
    - see #project-base-diff to update your project
- remove usage of article placement ([#2776](https://github.com/shopsys/shopsys/pull/2776))
    - placement `Shopsys\FrameworkBundle\Model\Article\Article::PLACEMENT_TOP_MENU` was removed along with the usages
    - already existing articles with this placement will be migrated to `Shopsys\FrameworkBundle\Model\Article\Article::PLACEMENT_NONE` placement
        - if you intend to keep using this placement, you can skip DB Migration `Shopsys\FrameworkBundle\Migrations\Version20230907132822` 
    - see #project-base-diff to update your project
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
    - see #project-base-diff to update your project
- remove misleading url addresses list from administration ([#2782](https://github.com/shopsys/shopsys/pull/2782))
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
- Products - Exposed in Stores, Category - SVG icon, and Category - Short description have been removed ([#2777](https://github.com/shopsys/shopsys/pull/2777))
    - if you use this functionality (e.g. from Commerce Cloud version), you can skip DB migration 20230908095905
- adjust test for variant creation from products with images ([#2802](https://github.com/shopsys/shopsys/pull/2802))
    - edit Tests\App\Functional\Model\Product\ProductVariantCreationTest::testVariantWithImageCanBeCreated
        ```diff
            $mainVariant = $this->productVariantFacade->createVariant($mainProduct, $variants);

            $this->assertTrue($mainVariant->isMainVariant());
        -   $this->assertContainsAllVariants([$mainProduct, ...$variants], $mainVariant);
        +   $this->assertContainsAllVariants($variants, $mainVariant);
        ```
- rewrite the `GetOrdersAsAuthenticatedCustomerUserTest` test ([#2805](https://github.com/shopsys/shopsys/pull/2805))
    - see #project-base-diff to update your project
