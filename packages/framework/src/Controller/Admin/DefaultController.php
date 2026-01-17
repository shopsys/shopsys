<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Statistics\StatisticsFacade;
use Shopsys\FrameworkBundle\Model\Statistics\StatisticsProcessingFacade;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AdminBaseController
{
    protected const int PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR = 7;

    public function __construct(
        protected readonly StatisticsFacade $statisticsFacade,
        protected readonly StatisticsProcessingFacade $statisticsProcessingFacade,
        protected readonly CronModuleFacade $cronModuleFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly CronConfig $cronConfig,
        protected readonly CronFacade $cronFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
        protected readonly TransferIssueFacade $transferIssueFacade,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
        protected readonly ArrayDataSourceFactory $arrayDataSourceFactory,
    ) {
    }

    #[Route(path: '/dashboard/')]
    #[RequireRole(SystemRole::ADMIN)]
    public function dashboardAction(): Response
    {
        $registeredInLastTwoWeeks = $this->statisticsFacade->getCustomersRegistrationsCountByDayInLastTwoWeeks();
        $registeredInLastTwoWeeksDates = $this->statisticsProcessingFacade->getDateTimesFormattedToLocaleFormat(
            $registeredInLastTwoWeeks,
        );
        $registeredInLastTwoWeeksCounts = $this->statisticsProcessingFacade->getCounts($registeredInLastTwoWeeks);
        $newOrdersCountByDayInLastTwoWeeks = $this->statisticsFacade->getNewOrdersCountByDayInLastTwoWeeks();
        $newOrdersInLastTwoWeeksDates = $this->statisticsProcessingFacade->getDateTimesFormattedToLocaleFormat(
            $newOrdersCountByDayInLastTwoWeeks,
        );
        $newOrdersInLastTwoWeeksCounts = $this->statisticsProcessingFacade->getCounts(
            $newOrdersCountByDayInLastTwoWeeks,
        );
        $transferIssuesCount = $this->transferIssueFacade->getTransferIssuesCountFrom($this->getCurrentAdministrator()->getTransferIssuesLastSeenDateTime());

        $quickProductSearchData = new QuickSearchFormData();
        $quickProductSearchForm = $this->createForm(QuickSearchFormType::class, $quickProductSearchData, [
            'action' => $this->generateUrl('admin_product_list'),
        ]);

        $currentCountOfOrders = $this->statisticsFacade->getOrdersCount(static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR);
        $previousCountOfOrders = $this->statisticsFacade->getOrdersCount(
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR * 2,
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR,
        );

        $ordersTrend = $this->getTrendDifference($previousCountOfOrders, $currentCountOfOrders);

        $currentCountOfNewCustomers = $this->statisticsFacade->getNewCustomersCount(
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR,
        );
        $previousCountOfNewCustomers = $this->statisticsFacade->getNewCustomersCount(
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR * 2,
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR,
        );

        $newCustomersTrend = $this->getTrendDifference($previousCountOfNewCustomers, $currentCountOfNewCustomers);

        $currentValueOfOrders = $this->statisticsFacade->getOrdersValue(static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR);
        $previousValueOfOrders = $this->statisticsFacade->getOrdersValue(
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR * 2,
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR,
        );

        $ordersValueTrend = $this->getTrendDifference($previousValueOfOrders, $currentValueOfOrders);

        return $this->render(
            '@ShopsysAdministration/content/default/index.html.twig',
            [
                'registeredInLastTwoWeeksLabels' => $registeredInLastTwoWeeksDates,
                'registeredInLastTwoWeeksValues' => $registeredInLastTwoWeeksCounts,
                'newOrdersInLastTwoWeeksLabels' => $newOrdersInLastTwoWeeksDates,
                'newOrdersInLastTwoWeeksValues' => $newOrdersInLastTwoWeeksCounts,
                'quickProductSearchForm' => $quickProductSearchForm->createView(),
                'newCustomers' => $currentCountOfNewCustomers,
                'newCustomersTrend' => $newCustomersTrend,
                'newOrders' => $currentCountOfOrders,
                'newOrdersTrend' => $ordersTrend,
                'ordersValue' => $currentValueOfOrders,
                'ordersValueTrend' => $ordersValueTrend,
                'transferIssuesCount' => $transferIssuesCount,
                'cronGridViews' => $this->getCronGridViews(),
            ],
        );
    }

    protected function getTrendDifference(int $previous, int $current): int
    {
        if ($previous === 0 && $current === 0) {
            $trend = 0;
        } elseif ($previous === 0) {
            $trend = 100;
        } else {
            $trend = (int)round(($current / $previous - 1) * 100);
        }

        return $trend;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView[]|null
     */
    protected function getCronGridViews(): ?array
    {
        if ($this->getParameter('shopsys.display_cron_overview_for_superadmin_only') === true
            && $this->isGranted(SystemRole::SUPER_ADMIN) === false
        ) {
            return null;
        }

        $cronInstances = $this->cronFacade->getInstanceNames();
        $gridViews = [];

        foreach ($cronInstances as $cronInstance) {
            $gridViews[$cronInstance] = $this->createCronGridViewForInstance($cronInstance);
        }

        return $gridViews;
    }

    protected function createCronGridViewForInstance(string $instanceName): GridView
    {
        $cronModules = $this->cronModuleFacade->findAllIndexedByServiceId();
        $cronConfigs = $this->cronConfig->getCronModuleConfigsForInstance($instanceName);

        $dataSource = $this->arrayDataSourceFactory->create(
            $this->prepareDataForCronInstanceGridView($cronConfigs, $cronModules),
        );

        $cronListGrid = $this->gridFactory->create('cronList', $dataSource, SystemRole::ADMIN);

        $cronListGrid->addColumn('name', 'name', t('Name'));
        $cronListGrid->addColumn('readableFrequency', 'readableFrequency', t('Frequency'));
        $cronListGrid->addColumn('lastStartedAt', 'lastStartedAt', t('Last started at'));
        $cronListGrid->addColumn('lastFinishedAt', 'lastFinishedAt', t('Last finished at'));
        $cronListGrid->addColumn(
            'lastDuration',
            'lastDuration',
            t('Last duration'),
            false,
            ['help' => t('(minutes:seconds)')],
        );
        $cronListGrid->addColumn(
            'minimalDuration',
            'minimalDuration',
            t('Min duration'),
            false,
            ['help' => t('Minimal duration from all runs in last seven days') . ' ' . t('(minutes:seconds)')],
        );
        $cronListGrid->addColumn(
            'averageDuration',
            'averageDuration',
            t('Avg duration'),
            false,
            ['help' => t('Average duration from all runs in last seven days') . ' ' . t('(minutes:seconds)')],
        );
        $cronListGrid->addColumn(
            'maximalDuration',
            'maximalDuration',
            t('Max duration'),
            false,
            ['help' => t('Maximal duration from all runs in last seven days') . ' ' . t('(minutes:seconds)')],
        );
        $cronListGrid->addColumn('status', 'status', t('Status'));

        if ($this->isGranted(SystemRole::SUPER_ADMIN)) {
            $cronListGrid->addColumn('actions', 'actions', t('Modifications'))->setClassAttribute('text-nowrap text-end');
        }

        $cronListGrid->setTitle(t('Cron overview (%instanceName%)', ['%instanceName%' => $instanceName]));

        $cronListGrid->setTheme('@ShopsysAdministration/content/default/cronListGrid.html.twig');

        return $cronListGrid->createView();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[] $cronConfigs
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModule[] $cronModules
     * @return array<int, array<string, bool|\DateTimeInterface|int|string|null>>
     */
    public function prepareDataForCronInstanceGridView(array $cronConfigs, array $cronModules): array
    {
        $data = [];

        $cronModuleDurationsIndexedByCronModuleId = $this->cronModuleFacade->getCronCalculatedDurationsIndexedByServiceId();

        foreach ($cronConfigs as $cronConfig) {
            if (array_key_exists($cronConfig->getServiceId(), $cronModules) === false) {
                $cronModule = $this->cronModuleFacade->getCronModuleByServiceId($cronConfig->getServiceId());
            } else {
                $cronModule = $cronModules[$cronConfig->getServiceId()];
            }

            $cronDurations = $cronModuleDurationsIndexedByCronModuleId[$cronConfig->getServiceId()] ?? null;

            $minimalDuration = $cronDurations === null || $cronDurations['minimalDuration'] === null ? null : (int)$cronDurations['minimalDuration'];
            $maximalDuration = $cronDurations === null || $cronDurations['maximalDuration'] === null ? null : (int)$cronDurations['maximalDuration'];
            $averageDuration = $cronDurations === null || $cronDurations['averageDuration'] === null ? null : (int)$cronDurations['averageDuration'];

            $data[] = [
                'id' => $cronModule->getServiceId(),
                'name' => $cronConfig->getReadableName() ?? $cronModule->getServiceId(),
                'lastStartedAt' => $cronModule->getLastStartedAt(),
                'lastFinishedAt' => $cronModule->getLastFinishedAt(),
                'lastDuration' => $cronModule->getLastDuration(),
                'status' => $cronModule->getStatus(),
                'enabled' => $cronModule->isEnabled(),
                'readableFrequency' => $cronConfig->getReadableFrequency(),
                'scheduled' => $cronModule->isScheduled(),
                'actions' => null,
                'minimalDuration' => $minimalDuration,
                'maximalDuration' => $maximalDuration,
                'averageDuration' => $averageDuration,
                'cronTimeoutSecs' => $cronConfig->getTimeoutIteratedCronSec(),
            ];
        }

        return $data;
    }

    #[Route(path: '/cron/schedule/{serviceId}')]
    #[SuperAdminOnly]
    public function scheduleCronAction(string $serviceId): Response
    {
        $this->cronModuleFacade->schedule($serviceId);
        $this->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was scheduled', ['%serviceId%' => $serviceId]),
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }

    #[Route(path: '/cron/disable/{serviceId}')]
    #[SuperAdminOnly]
    public function cronDisableAction(string $serviceId): Response
    {
        $this->cronModuleFacade->disableCronModuleByServiceId($serviceId);
        $this->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was disabled', ['%serviceId%' => $serviceId]),
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }

    #[Route(path: '/cron/enable/{serviceId}')]
    #[SuperAdminOnly]
    public function cronEnableAction(string $serviceId): Response
    {
        $this->cronModuleFacade->enableCronModuleByServiceId($serviceId);
        $this->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was enabled', ['%serviceId%' => $serviceId]),
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }

    #[Route(path: '/cron/detail/{serviceId}')]
    #[RequireRole(SystemRole::ADMIN)]
    public function cronDetailAction(string $serviceId): Response
    {
        if ($this->getParameter('shopsys.display_cron_overview_for_superadmin_only') === true) {
            $this->denyAccessUnlessGranted(SystemRole::SUPER_ADMIN);
        }

        $cronModule = $this->cronModuleFacade->getCronModuleByServiceId($serviceId);
        $cronModuleRuns = $this->cronModuleFacade->getAllRunsByCronModule($cronModule);
        $cronConfig = $this->cronConfig->getCronModuleConfigByServiceId($serviceId);

        $data = [];

        foreach ($cronModuleRuns as $cronModuleRun) {
            $data[] = [
                'id' => $cronModuleRun->getId(),
                'startedAt' => $cronModuleRun->getStartedAt(),
                'finishedAt' => $cronModuleRun->getFinishedAt(),
                'duration' => $cronModuleRun->getDuration(),
                'status' => $cronModuleRun->getStatus(),
                'actions' => null,
            ];
        }

        $queryBuilder = $this->cronModuleFacade->getRunsByCronModuleQueryBuilder($cronModule);
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'cmr.id');

        $cronRunsListGrid = $this->gridFactory->create('cronRunsList', $dataSource, SystemRole::ADMIN);

        $cronRunsListGrid->addColumn('startedAt', 'cmr.startedAt', t('Started at'));
        $cronRunsListGrid->addColumn('finishedAt', 'cmr.finishedAt', t('Finished at'));
        $cronRunsListGrid->addColumn('duration', 'cmr.duration', t('Duration (mm:ss)'));
        $cronRunsListGrid->addColumn('status', 'cmr.status', t('Status'));
        $cronRunsListGrid->setTheme(
            '@ShopsysAdministration/content/default/cronModuleRunsListGrid.html.twig',
            [
                'cronTimeoutSecs' => $cronConfig->getTimeoutIteratedCronSec(),
            ],
        );
        $cronRunsListGrid->enablePaging();
        $cronRunsListGrid->setDefaultLimit(100);
        $cronRunsListGrid->setTitle(t('Cron runs'));

        $this->breadcrumbOverrider->overrideLastItem(
            t('Cron detail - %name%', ['%name%' => $cronConfig->getReadableName() ?? $cronModule->getServiceId()]),
        );

        return $this->render(
            '@ShopsysAdministration/content/default/cronDetail.html.twig',
            [
                'cronRunsGridView' => $cronRunsListGrid->createView(),
                'cronGraphValues' => array_column($data, 'duration'),
                'cronGraphLabels' => array_map(
                    function ($data) {
                        return $this->dateTimeFormatterExtension->formatDate($data['startedAt']);
                    },
                    $data,
                ),
                'cronName' => $cronConfig->getReadableName() ?? $cronModule->getServiceId(),
                'cronTimeoutSecs' => $cronConfig->getTimeoutIteratedCronSec(),
            ],
        );
    }
}
