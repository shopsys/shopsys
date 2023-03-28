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
