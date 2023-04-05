# [Upgrade from v11.0.0 to v11.0.1-dev](https://github.com/shopsys/shopsys/compare/v11.0.0...master)

This guide contains instructions to upgrade from version v11.0.0 to v11.0.1-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
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
    - see #project-base-diff to update your project

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
    - see #project-base-diff to update your project
- enable custom period for running crons ([#2581](https://github.com/shopsys/shopsys/pull/2581))
    - `Shopsys\FrameworkBundle\Command\CronCommand` class:
        - method `getCurrentRoundedTime` changed its interface:
        ```diff
            function getCurrentRoundedTime(
        +       int $runEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT
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
        +       CronModuleConfig|null $cronConfig = null
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
        +       int $runEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
        +       int $timeoutIteratedCronSec = CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT
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
    - see #project-base-diff to update your project
- update your extended phing targets in `build.xml` to have their outputs more verbose when an error occurs ([#2581](https://github.com/shopsys/shopsys/pull/2581))
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
    - see #project-base-diff to update your project
- added ability to change content of robots.txt file through administration ([#2591](https://github.com/shopsys/shopsys/pull/2591))
  - `App\Controller\Front\RobotsController` class:
    - method `__construct` changed its interface:
    ```diff
        public function __construct(
            string $sitemapsUrlPrefix,
            Domain $domain,
            SitemapFilePrefixer $sitemapFilePrefixer,
    +       SeoSettingFacade $seoSettingFacade,
        ) {
    ```
  - if custom storefront is used through FE API then change must be implemented by your own
  - see #project-base-diff to update your project
