# [Upgrade from v11.0.0 to v11.1.0](https://github.com/shopsys/shopsys/compare/v11.0.0...v11.1.0)

This guide contains instructions to upgrade from version v11.0.0 to v11.1.0.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/v11.1.0/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- add detailed cron run information for administrators ([#2581](https://github.com/shopsys/shopsys/pull/2581))
    - `Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade` class:
        - method `__construct` changed its interface:
          ```diff
          function __construct(
              EntityManagerInterface $em,
              CronModuleRepository $cronModuleRepository,
              CronFilter $cronFilter
              EntityManagerInterface $em,
              CronModuleRepository $cronModuleRepository,
              CronFilter $cronFilter,
          +   ?CronModuleRunFactory $cronModuleRunFactory = null,
          )
          ```
    - `Shopsys\FrameworkBundle\Controller\Admin\DefaultController` class:
        - method `__construct` changed its interface:
          ```diff
          function __construct(
              StatisticsFacade $statisticsFacade,
              StatisticsProcessingFacade $statisticsProcessingFacade,
              MailTemplateFacade $mailTemplateFacade,
              UnitFacade $unitFacade,
              Setting $setting,
              AvailabilityFacade $availabilityFacade,
              CronModuleFacade $cronModuleFacade,
              GridFactory $gridFactory,
              CronConfig $cronConfig,
              CronFacade $cronFacade,
          +   ?BreadcrumbOverrider $breadcrumbOverrider = null,
          +   ?DateTimeFormatterExtension $dateTimeFormatterExtension = null,
          ) {
          ```
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/7151bb9cca66e45e85d5d39d0e05224dab73b323) to update your project

- retrieving adverts on product list now requires to define category ([#2583](https://github.com/shopsys/shopsys/pull/2583))
    - `Shopsys\FrameworkBundle\Model\Advert\AdvertRepository` class:
        - method `getAdvertByPositionQueryBuilder` changed its interface:
        ```diff
        - function getAdvertByPositionQueryBuilder($positionName, $domainId)
        + function getAdvertByPositionQueryBuilder($positionName, $domainId, $category = null)
        ```
    - `Shopsys\FrameworkBundle\Model\Advert\AdvertRepository` class:
        - method `getAdvertByPositionQueryBuilder` changed its interface:
        ```diff
        - function findRandomAdvertByPosition($positionName, $domainId)
        + function findRandomAdvertByPosition($positionName, $domainId, $category = null)
        ```
    - `Shopsys\FrameworkBundle\Model\Advert\AdvertFacade` class:
        - method `findRandomAdvertByPositionOnCurrentDomain` changed its interface:
        ```diff
        - function findRandomAdvertByPositionOnCurrentDomain($positionName)
        + function findRandomAdvertByPositionOnCurrentDomain($positionName, $category = null)
        ```
    - `Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade` class:
        - method `getVisibleAdvertsByDomainIdAndPositionName` changed its interface:
        ```diff
        - function getVisibleAdvertsByDomainIdAndPositionName(int $domainId, string $positionName): array
        + function getVisibleAdvertsByDomainIdAndPositionName(int $domainId, string $positionName, ?Category $category = null): array
        ```
    - `Shopsys\FrontendApiBundle\Model\Advert\AdvertRepository` class:
        - method `getVisibleAdvertsByPositionNameAndDomainId` changed its interface:
        ```diff
        - function getVisibleAdvertsByPositionNameAndDomainId(int $domainId, string $positionName): array
        + function getVisibleAdvertsByPositionNameAndDomainId(int $domainId, string $positionName, ?Category $category = null): array
        ```
    - `Shopsys\FrontendApiBundle\Model\Advert\AdvertRepository` class:
        - method `getVisibleAdvertsByPositionNameQueryBuilder` changed its interface:
        ```diff
        - function getVisibleAdvertsByPositionNameQueryBuilder(int $domainId, string $positionName)
        + function getVisibleAdvertsByPositionNameQueryBuilder(int $domainId, string $positionName, ?Category $category = null)
        ```
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/0e704c0c09860a4b570cceeb794b638d7e655a53) to update your project
- enable custom period for running crons ([#2584](https://github.com/shopsys/shopsys/pull/2584))
    - `Shopsys\FrameworkBundle\Command\CronCommand` class:
        - method `getCurrentRoundedTime` changed its interface:
        ```diff
            function getCurrentRoundedTime(
        +       int $runEveryMin,
            )
        ```
    - `Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor` class:
        - method `__construct` changed its interface:
        ```diff
            function __construct(
                int $secondsTimeout,
        +       protected ?CronConfig $cronConfig = null,
            )
        ```
        - method `canRun` changed its interface:
        ```diff
            function canRun(
        +       CronModuleConfig $cronConfig,
            ): bool
        ```
    - `Shopsys\FrameworkBundle\Command\CronCommand` class:
        - method `registerCronModuleInstance` changed its interface:
        ```diff
            public function registerCronModuleInstance(
                $service,
                string $serviceId,
                string $timeHours,
                string $timeMinutes,
                string $instanceName,
                ?string $readableName = null,
        +       int $runEveryMin,
        +       int $timeoutIteratedCronSec,
            ): void {
        ```
    - `Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig` class:
        - method `__construct` changed its interface:
        ```diff
            public function __construct(
                object $service,
                string $serviceId,
                string $timeHours,
                string $timeMinutes,
                ?string $readableName = null,
        +       int $runEveryMin = self::RUN_EVERY_MIN_DEFAULT,
        +       int $timeoutIteratedCronSec = self::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            ) {
        ```
    - constant `Shopsys\FrameworkBundle\Controller\Admin\DefaultController::HOUR_IN_SECONDS` is now deprecated and will be removed in next major
    - method `Shopsys\FrameworkBundle\Controller\Admin\DefaultController::getFormattedDuration()` is now deprecated and will be removed in next major, use `Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension::formatDurationInSeconds()` instead
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/00631773e23a930236d17ac102d7b15a085731f1) to update your project
- update your extended phing targets in `build.xml` to have their outputs more verbose when an error occurs ([#2507](https://github.com/shopsys/shopsys/pull/2507))
    - add `<arg value="--verbose"/>` to your extended phing targets in build.xml that are based on `bin/console` commands
    - example on `bin/console` based command update:
    ```diff
        <target name="error-pages-generate" depends="prod-warmup,redis-check" description="Generates error pages displayed in production environment.">
            <exec executable="${path.php.executable}" passthru="true" checkreturn="true">
                <arg value="${path.bin-console}"/>
                <arg value="shopsys:error-page:generate-all"/>
    +           <arg value="--verbose"/>
            </exec>
        </target>
    ```
- remove no longer necessary encapsulation of sending OrderMail by checking if it is enabled as it is now done directly in OrderMailFacade ([#2588](https://github.com/shopsys/shopsys/pull/2588))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/338cbdc628d2a13444b0925c35a15b0f8cb453e9) to update your project
- added ability to change content of robots.txt file through administration ([#2591](https://github.com/shopsys/shopsys/pull/2591))
    - `App\Controller\Front\RobotsController` class:
        - method `__construct` changed its interface:
        ```diff
            public function __construct(
                string $sitemapsUrlPrefix,
                Domain $domain,
                SitemapFilePrefixer $sitemapFilePrefixer,
        +       ?SeoSettingFacade $seoSettingFacade = null,
            ) {
        ```
    - if you have implemented a custom storefront using frontend API then you should consider implementing this functionality into your storefront
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/ca877c916c465e6edb0bca5492789df2baec19a6) to update your project
- enable login rate limits to prevent brute force attacks ([#2599](https://github.com/shopsys/shopsys/pull/2599) and [#2613](https://github.com/shopsys/shopsys/pull/2613))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/6a99f2d964d3b7b5fac99443a74de3e9702dee9c) and then [project-base-diff](https://github.com/shopsys/project-base/commit/f7f209bd48e706ebabb86096db11add7330eda60) to update your project
- improve your installation on macOS by replacing `Docker-sync` with `Mutagen` ([#2593](https://github.com/shopsys/shopsys/pull/2593))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/cc3736dedc117b1b92fcffa2e1eecedaec011003) to update your project
- improve config folder structure ([#2607](https://github.com/shopsys/shopsys/pull/2607))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/1b5cbf13acba7b14a389fc1531ac1fc3e6c60517) to update your project
- FE-API: fix return value for authentication failure ([#2387](https://github.com/shopsys/shopsys/pull/2387))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/e53cfce2c53882349e562b9112e3a912bf76396f) to update your project
- fix running acceptance tests on local machines ([#2610](https://github.com/shopsys/shopsys/pull/2610))
    - `Tests\FrameworkBundle\Test\Codeception\ActorInterface` interface:
        - method `moveMouseOverByCss` has been removed
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/fa84b398167a1f1d104c87d262600fca3f5b2dcf) to update your project
- apply new coding standards for requiring blank line before break, continue, declare, do, for, foreach, if, return, switch, throw, try, while and yield statements ([#2128](https://github.com/shopsys/shopsys/pull/2128))
    - run `php phing ecs-fix` to apply new standards
- add ability to set e-mail whitelists per domain in the administration ([#2592](https://github.com/shopsys/shopsys/pull/2592))
    - `MAILER_MASTER_EMAIL_ADDRESS` ENV variable is now deprecated and will be removed in next major
    - `MAILER_DELIVERY_WHITELIST` ENV variable is now deprecated and will be removed in next major
    - you need to convert email whitelist setting from `MAILER_DELIVERY_WHITELIST` ENV variable to `mailWhitelist` database setting through database migration if you want to use whitelist configuration through administration
        - see `\Shopsys\FrameworkBundle\Migrations\Version20230405111441`
    - constructor `Shopsys\FrameworkBundle\Controller\Admin\SuperadminController::__construct()` changed its interface:
        ```diff
            public function __construct(
                ModuleList $moduleList,
                ModuleFacade $moduleFacade,
                PricingSetting $pricingSetting,
                DelayedPricingSetting $delayedPricingSetting,
                GridFactory $gridFactory,
                Localization $localization,
                LocalizedRouterFactory $localizedRouterFactory
        +       protected /* readonly */ ?MailSettingFacade $mailSettingFacade = null,
        +       protected /* readonly */ ?MailerSettingProvider $mailerSettingProvider = null,
        +       protected /* readonly */ ?AdminDomainTabsFacade $adminDomainTabsFacade = null,
            ) {
        ```
    - method `Shopsys\FrameworkBundle\Model\Mail\Mailer::send()` is now deprecated and will be removed in next major, use `sendForDomain` instead
    - `Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider` class:
        - method `__construct` changed its interface:
            ```diff
                public function __construct(
                    MailerInterface $symfonyMailer,
                    MailTemplateFacade $mailTemplateFacade,
                    LoggerInterface $logger,
                    protected readonly ?bool $whitelistForced = null,
                    protected /* readonly */ ?MailSettingFacade $mailSettingFacade = null,
                ) {
            ```
        - property `$mailerMasterEmailAddress` is now deprecated and will be removed in next major
        - property `$mailerWhitelistExpressions` is now deprecated and will be removed in next major
        - method `getMailerMasterEmailAddress()` is now deprecated and will be removed in next major
        - method `isMailerMasterEmailSet()` is now deprecated and will be removed in next major
        - method `getMailerWhitelistExpressions()` is now deprecated and will be removed in next major
    - constructor `Shopsys\FrameworkBundle\Twig\MailerSettingExtension::__construct()` changed its interface:
        ```diff
            public function __construct(
                MailerSettingProvider $mailerSettingProvider,
                Environment $twigEnvironment,
        +       protected /* readonly */ ?Domain $domain = null,
            ) {
        ```
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/0e7d716eba788dece7186fdfac0558855453acce) to update your project
- exclude 405 errors from logging ([#2666](https://github.com/shopsys/shopsys/pull/2666))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/c4a5841aac5227bc99f597d799e272033829dbcf) to update your project
