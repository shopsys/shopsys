<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Statistics\StatisticsFacade;
use Shopsys\FrameworkBundle\Model\Statistics\StatisticsProcessingFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AdminBaseController
{
    use SetterInjectionTrait;

    protected const PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR = 7;
    protected const HOUR_IN_SECONDS = 60 * 60;
    public const EXPECTED_MAXIMUM_CRON_RUNTIME_IN_SECONDS = 4 * 60;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\StatisticsFacade $statisticsFacade
     * @param \Shopsys\FrameworkBundle\Model\Statistics\StatisticsProcessingFacade $statisticsProcessingFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade $cronModuleFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig $cronConfig
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider|null $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension|null $dateTimeFormatterExtension
     */
    public function __construct(
        protected readonly StatisticsFacade $statisticsFacade,
        protected readonly StatisticsProcessingFacade $statisticsProcessingFacade,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly UnitFacade $unitFacade,
        protected readonly Setting $setting,
        protected readonly AvailabilityFacade $availabilityFacade,
        protected readonly CronModuleFacade $cronModuleFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly CronConfig $cronConfig,
        protected readonly CronFacade $cronFacade,
        protected ?BreadcrumbOverrider $breadcrumbOverrider = null,
        protected ?DateTimeFormatterExtension $dateTimeFormatterExtension = null,
    ) {
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setBreadcrumbOverrider(BreadcrumbOverrider $breadcrumbOverrider): void
    {
        $this->setDependency($breadcrumbOverrider, 'breadcrumbOverrider');
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setDateTimeFormatterExtension(DateTimeFormatterExtension $dateTimeFormatterExtension): void
    {
        $this->setDependency($dateTimeFormatterExtension, 'dateTimeFormatterExtension');
    }

    /**
     * @Route("/dashboard/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dashboardAction(): Response
    {
        $registeredInLastTwoWeeks = $this->statisticsFacade->getCustomersRegistrationsCountByDayInLastTwoWeeks();
        $registeredInLastTwoWeeksDates = $this->statisticsProcessingFacade->getDateTimesFormattedToLocaleFormat(
            $registeredInLastTwoWeeks
        );
        $registeredInLastTwoWeeksCounts = $this->statisticsProcessingFacade->getCounts($registeredInLastTwoWeeks);
        $newOrdersCountByDayInLastTwoWeeks = $this->statisticsFacade->getNewOrdersCountByDayInLastTwoWeeks();
        $newOrdersInLastTwoWeeksDates = $this->statisticsProcessingFacade->getDateTimesFormattedToLocaleFormat(
            $newOrdersCountByDayInLastTwoWeeks
        );
        $newOrdersInLastTwoWeeksCounts = $this->statisticsProcessingFacade->getCounts(
            $newOrdersCountByDayInLastTwoWeeks
        );

        $quickProductSearchData = new QuickSearchFormData();
        $quickProductSearchForm = $this->createForm(QuickSearchFormType::class, $quickProductSearchData, [
            'action' => $this->generateUrl('admin_product_list'),
        ]);

        $currentCountOfOrders = $this->statisticsFacade->getOrdersCount(static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR);
        $previousCountOfOrders = $this->statisticsFacade->getOrdersCount(
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR * 2,
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR
        );

        $ordersTrend = $this->getTrendDifference($previousCountOfOrders, $currentCountOfOrders);

        $currentCountOfNewCustomers = $this->statisticsFacade->getNewCustomersCount(
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR
        );
        $previousCountOfNewCustomers = $this->statisticsFacade->getNewCustomersCount(
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR * 2,
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR
        );

        $newCustomersTrend = $this->getTrendDifference($previousCountOfNewCustomers, $currentCountOfNewCustomers);

        $currentValueOfOrders = $this->statisticsFacade->getOrdersValue(static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR);
        $previousValueOfOrders = $this->statisticsFacade->getOrdersValue(
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR * 2,
            static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR
        );

        $ordersValueTrend = $this->getTrendDifference($previousValueOfOrders, $currentValueOfOrders);

        $this->addWarningMessagesOnDashboard();

        return $this->render(
            '@ShopsysFramework/Admin/Content/Default/index.html.twig',
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
                'cronGridViews' => $this->getCronGridViews(),
            ]
        );
    }

    protected function addWarningMessagesOnDashboard(): void
    {
        if ($this->mailTemplateFacade->existsTemplateWithEnabledSendingHavingEmptyBodyOrSubject()) {
            $this->addErrorFlashTwig(
                t('<a href="{{ url }}">Some required email templates are not fully set.</a>'),
                [
                    'url' => $this->generateUrl('admin_mail_template'),
                ]
            );
        }

        if (count($this->unitFacade->getAll()) === 0) {
            $this->addErrorFlashTwig(
                t('<a href="{{ url }}">There are no units, you need to create some.</a>'),
                [
                    'url' => $this->generateUrl('admin_unit_list'),
                ]
            );
        }

        if ($this->setting->get(Setting::DEFAULT_UNIT) === 0) {
            $this->addErrorFlashTwig(
                t('<a href="{{ url }}">Default unit is not set.</a>'),
                [
                    'url' => $this->generateUrl('admin_unit_list'),
                ]
            );
        }

        if (count($this->availabilityFacade->getAll()) === 0) {
            $this->addErrorFlashTwig(
                t('<a href="{{ url }}">There are no availabilities, you need to create some.</a>'),
                [
                    'url' => $this->generateUrl('admin_availability_list'),
                ]
            );
        }

        if ($this->setting->get(Setting::DEFAULT_AVAILABILITY_IN_STOCK) === 0) {
            $this->addErrorFlashTwig(
                t('<a href="{{ url }}">Default product in stock availability is not set.</a>'),
                [
                    'url' => $this->generateUrl('admin_availability_list'),
                ]
            );
        }
    }

    /**
     * @param int $previous
     * @param int $current
     * @return int
     */
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
            && $this->isGranted(Roles::ROLE_SUPER_ADMIN) === false
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

    /**
     * @param string $instanceName
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView
     */
    protected function createCronGridViewForInstance(string $instanceName): GridView
    {
        $cronModules = $this->cronModuleFacade->findAllIndexedByServiceId();
        $cronConfigs = $this->cronConfig->getCronModuleConfigsForInstance($instanceName);

        $data = [];

        $cronModuleDurationsIndexedByCronModuleId = $this->cronModuleFacade->getCronCalculatedDurationsIndexedByServiceId();

        foreach ($cronConfigs as $cronConfig) {
            if (array_key_exists($cronConfig->getServiceId(), $cronModules) === false) {
                $cronModule = $this->cronModuleFacade->getCronModuleByServiceId($cronConfig->getServiceId());
            } else {
                $cronModule = $cronModules[$cronConfig->getServiceId()];
            }

            $cronDurations = $cronModuleDurationsIndexedByCronModuleId[$cronConfig->getServiceId()] ?? null;

            $data[] = [
                'id' => $cronModule->getServiceId(),
                'name' => $cronConfig->getReadableName() ?? $cronModule->getServiceId(),
                'lastStartedAt' => $cronModule->getLastStartedAt(),
                'lastFinishedAt' => $cronModule->getLastFinishedAt(),
                'lastDuration' => $this->getFormattedDuration($cronModule->getLastDuration()),
                'status' => $cronModule->getStatus(),
                'enabled' => $cronModule->isEnabled(),
                'readableFrequency' => $cronConfig->getReadableFrequency(),
                'scheduled' => $cronModule->isScheduled(),
                'actions' => null,
                'minimalDuration' => $this->getFormattedDuration(
                    $cronDurations === null || $cronDurations['minimalDuration'] === null ? null : (int)$cronDurations['minimalDuration']
                ),
                'maximalDuration' => $this->getFormattedDuration(
                    $cronDurations === null || $cronDurations['maximalDuration'] === null ? null : (int)$cronDurations['maximalDuration']
                ),
                'averageDuration' => $this->getFormattedDuration(
                    $cronDurations === null || $cronDurations['averageDuration'] === null ? null : (int)$cronDurations['averageDuration']
                ),
            ];
        }

        $dataSource = new ArrayDataSource($data);

        $cronListGrid = $this->gridFactory->create('cronList', $dataSource);

        $cronListGrid->addColumn('name', 'name', t('Name'), false);
        $cronListGrid->addColumn('readableFrequency', 'readableFrequency', t('Frequency'), false);
        $cronListGrid->addColumn('lastStartedAt', 'lastStartedAt', t('Last started at'), false);
        $cronListGrid->addColumn('lastFinishedAt', 'lastFinishedAt', t('Last finished at'), false);
        $cronListGrid->addColumn('lastDuration', 'lastDuration', t('Last duration (mm:ss)'), false)->setClassAttribute(
            'table-col'
        );
        $cronListGrid->addColumn('minimalDuration', 'minimalDuration', t('Min duration (mm:ss)'), false)->setClassAttribute(
            'table-col'
        );
        $cronListGrid->addColumn('averageDuration', 'averageDuration', t('Avg duration (mm:ss)'), false)->setClassAttribute(
            'table-col'
        );

        $cronListGrid->addColumn('maximalDuration', 'maximalDuration', t('Max duration (mm:ss)'), false)->setClassAttribute(
            'table-col'
        );
        $cronListGrid->addColumn('status', 'status', t('Status'), false)->setClassAttribute('table-col');

        if ($this->isGranted(Roles::ROLE_SUPER_ADMIN)) {
            $cronListGrid->addColumn('actions', 'actions', t('Modifications'))->setClassAttribute(
                'table-grid__cell--actions column--superadmin'
            );
        }

        $cronListGrid->setTheme('@ShopsysFramework/Admin/Content/Default/cronListGrid.html.twig');

        return $cronListGrid->createView();
    }

    /**
     * @Route("/cron/schedule/{serviceId}")
     * @param string $serviceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function scheduleCronAction(string $serviceId): Response
    {
        $this->cronModuleFacade->schedule($serviceId);
        $this->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was scheduled', ['%serviceId%' => $serviceId])
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }

    /**
     * @Route("/cron/disable/{serviceId}")
     * @param string $serviceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cronDisableAction(string $serviceId): Response
    {
        $this->cronModuleFacade->disableCronModuleByServiceId($serviceId);
        $this->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was disabled', ['%serviceId%' => $serviceId])
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }

    /**
     * @Route("/cron/enable/{serviceId}")
     * @param string $serviceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cronEnableAction(string $serviceId): Response
    {
        $this->cronModuleFacade->enableCronModuleByServiceId($serviceId);
        $this->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was enabled', ['%serviceId%' => $serviceId])
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }

    /**
     * @param int|null $durationInSeconds
     * @return string
     */
    protected function getFormattedDuration(?int $durationInSeconds): string
    {
        if ($durationInSeconds === null) {
            return '';
        }

        $formattedHours = '';

        if ($durationInSeconds >= static::HOUR_IN_SECONDS) {
            $hours = (int)floor($durationInSeconds / static::HOUR_IN_SECONDS);
            $formattedHours .= $hours . ':';

            $durationInSeconds -= $hours * static::HOUR_IN_SECONDS;
        }

        return $formattedHours . date('i:s', $durationInSeconds);
    }

    /**
     * @Route("/cron/detail/{serviceId}")
     * @param string $serviceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cronDetailAction(string $serviceId): Response
    {
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
                'durationFormatted' => $this->getFormattedDuration($cronModuleRun->getDuration()),
                'status' => $cronModuleRun->getStatus(),
                'actions' => null,
            ];
        }

        $dataSource = new ArrayDataSource($data);

        $cronRunsListGrid = $this->gridFactory->create('cronRunsList', $dataSource);

        $cronRunsListGrid->addColumn('startedAt', 'startedAt', t('Started at'), false);
        $cronRunsListGrid->addColumn('finishedAt', 'finishedAt', t('Finished at'), false);
        $cronRunsListGrid->addColumn('duration', 'durationFormatted', t('Duration (mm:ss)'), false)->setClassAttribute(
            'table-col table-col-10'
        );
        $cronRunsListGrid->addColumn('status', 'status', t('Status'), false)->setClassAttribute('table-col table-col-10');
        $cronRunsListGrid->setTheme('@ShopsysFramework/Admin/Content/Default/cronModuleRunsListGrid.html.twig');

        $this->breadcrumbOverrider->overrideLastItem(
            t('Cron detail - %name%', ['%name%' => $cronConfig->getReadableName() ?? $cronModule->getServiceId()])
        );

        return $this->render(
            '@ShopsysFramework/Admin/Content/Default/cronDetail.html.twig',
            [
                'cronRunsGridView' => $cronRunsListGrid->createView(),
                'cronGraphValues' => array_column($data, 'duration'),
                'cronGraphLabels' => array_map(
                    function ($data) {
                        return $this->dateTimeFormatterExtension->formatDate($data['startedAt']);
                    },
                    $data
                ),
                'cronName' => $cronConfig->getReadableName() ?? $cronModule->getServiceId(),
            ]
        );
    }
}
