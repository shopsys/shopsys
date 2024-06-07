<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Statistics\StatisticsFacade;
use Shopsys\FrameworkBundle\Model\Statistics\StatisticsProcessingFacade;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AdminBaseController
{
    protected const PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR = 7;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\StatisticsFacade $statisticsFacade
     * @param \Shopsys\FrameworkBundle\Model\Statistics\StatisticsProcessingFacade $statisticsProcessingFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade $cronModuleFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig $cronConfig
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly StatisticsFacade $statisticsFacade,
        protected readonly StatisticsProcessingFacade $statisticsProcessingFacade,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly UnitFacade $unitFacade,
        protected readonly Setting $setting,
        protected readonly CronModuleFacade $cronModuleFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly CronConfig $cronConfig,
        protected readonly CronFacade $cronFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
        protected readonly TransferIssueFacade $transferIssueFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/dashboard/')]
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
                'transferIssuesCount' => $transferIssuesCount,
                'cronGridViews' => $this->getCronGridViews(),
            ],
        );
    }

    protected function addWarningMessagesOnDashboard(): void
    {
        if ($this->mailTemplateFacade->existsTemplateWithEnabledSendingHavingEmptyBodyOrSubject()) {
            $this->addErrorFlashTwig(
                t('<a href="{{ url }}">Some required email templates are not fully set.</a>'),
                [
                    'url' => $this->generateUrl('admin_mail_template'),
                ],
            );
        }

        if (count($this->unitFacade->getAll()) === 0) {
            $this->addErrorFlashTwig(
                t('<a href="{{ url }}">There are no units, you need to create some.</a>'),
                [
                    'url' => $this->generateUrl('admin_unit_list'),
                ],
            );
        }

        if ($this->setting->get(Setting::DEFAULT_UNIT) === 0) {
            $this->addErrorFlashTwig(
                t('<a href="{{ url }}">Default unit is not set.</a>'),
                [
                    'url' => $this->generateUrl('admin_unit_list'),
                ],
            );
        }

        foreach ($this->domain->getAll() as $domainConfig) {
            if ($this->setting->getForDomain(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $domainConfig->getId()) === null) {
                $this->addErrorFlashTwig(
                    t('<a href="{{ url }}">Terms and conditions article for domain {{ domainName }} is not set.</a>'),
                    [
                        'url' => $this->generateUrl('admin_legalconditions_termsandconditions'),
                        'domainName' => $domainConfig->getName(),
                    ],
                );
            }

            if ($this->setting->getForDomain(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainConfig->getId()) === null) {
                $this->addErrorFlashTwig(
                    t('<a href="{{ url }}">Privacy policy article for domain {{ domainName }} is not set.</a>'),
                    [
                        'url' => $this->generateUrl('admin_legalconditions_privacypolicy'),
                        'domainName' => $domainConfig->getName(),
                    ],
                );
            }

            if ($this->setting->getForDomain(Setting::USER_CONSENT_POLICY_ARTICLE_ID, $domainConfig->getId()) === null) {
                $this->addErrorFlashTwig(
                    t('<a href="{{ url }}">User consent policy article for domain {{ domainName }} is not set.</a>'),
                    [
                        'url' => $this->generateUrl('admin_userconsentpolicy_setting'),
                        'domainName' => $domainConfig->getName(),
                    ],
                );
            }
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

        $dataSource = new ArrayDataSource($data);

        $cronListGrid = $this->gridFactory->create('cronList', $dataSource);

        $cronListGrid->addColumn('name', 'name', t('Name'), false);
        $cronListGrid->addColumn('readableFrequency', 'readableFrequency', t('Frequency'), false);
        $cronListGrid->addColumn('lastStartedAt', 'lastStartedAt', t('Last started at'), false);
        $cronListGrid->addColumn('lastFinishedAt', 'lastFinishedAt', t('Last finished at'), false);
        $cronListGrid->addColumn('lastDuration', 'lastDuration', t('Last duration (mm:ss)'), false)->setClassAttribute(
            'table-col',
        );
        $cronListGrid->addColumn('minimalDuration', 'minimalDuration', t('Min duration (mm:ss)'), false)->setClassAttribute(
            'table-col',
        );
        $cronListGrid->addColumn('averageDuration', 'averageDuration', t('Avg duration (mm:ss)'), false)->setClassAttribute(
            'table-col',
        );

        $cronListGrid->addColumn('maximalDuration', 'maximalDuration', t('Max duration (mm:ss)'), false)->setClassAttribute(
            'table-col',
        );
        $cronListGrid->addColumn('status', 'status', t('Status'), false)->setClassAttribute('table-col');

        if ($this->isGranted(Roles::ROLE_SUPER_ADMIN)) {
            $cronListGrid->addColumn('actions', 'actions', t('Modifications'))->setClassAttribute(
                'table-grid__cell--actions column--superadmin',
            );
        }

        $cronListGrid->setTheme('@ShopsysFramework/Admin/Content/Default/cronListGrid.html.twig');

        return $cronListGrid->createView();
    }

    /**
     * @param string $serviceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/cron/schedule/{serviceId}')]
    public function scheduleCronAction(string $serviceId): Response
    {
        $this->cronModuleFacade->schedule($serviceId);
        $this->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was scheduled', ['%serviceId%' => $serviceId]),
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }

    /**
     * @param string $serviceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/cron/disable/{serviceId}')]
    public function cronDisableAction(string $serviceId): Response
    {
        $this->cronModuleFacade->disableCronModuleByServiceId($serviceId);
        $this->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was disabled', ['%serviceId%' => $serviceId]),
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }

    /**
     * @param string $serviceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/cron/enable/{serviceId}')]
    public function cronEnableAction(string $serviceId): Response
    {
        $this->cronModuleFacade->enableCronModuleByServiceId($serviceId);
        $this->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was enabled', ['%serviceId%' => $serviceId]),
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }

    /**
     * @param string $serviceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/cron/detail/{serviceId}')]
    public function cronDetailAction(string $serviceId): Response
    {
        if ($this->getParameter('shopsys.display_cron_overview_for_superadmin_only') === true) {
            $this->denyAccessUnlessGranted(Roles::ROLE_SUPER_ADMIN);
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
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'cmr.id');

        $cronRunsListGrid = $this->gridFactory->create('cronRunsList', $dataSource);

        $cronRunsListGrid->addColumn('startedAt', 'cmr.startedAt', t('Started at'), false);
        $cronRunsListGrid->addColumn('finishedAt', 'cmr.finishedAt', t('Finished at'), false);
        $cronRunsListGrid->addColumn('duration', 'cmr.duration', t('Duration (mm:ss)'), false)->setClassAttribute(
            'table-col table-col-10',
        );
        $cronRunsListGrid->addColumn('status', 'cmr.status', t('Status'), false)->setClassAttribute('table-col table-col-10');
        $cronRunsListGrid->setTheme(
            '@ShopsysFramework/Admin/Content/Default/cronModuleRunsListGrid.html.twig',
            [
                'cronTimeoutSecs' => $cronConfig->getTimeoutIteratedCronSec(),
            ],
        );
        $cronRunsListGrid->enablePaging();
        $cronRunsListGrid->setDefaultLimit(100);

        $this->breadcrumbOverrider->overrideLastItem(
            t('Cron detail - %name%', ['%name%' => $cronConfig->getReadableName() ?? $cronModule->getServiceId()]),
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
                    $data,
                ),
                'cronName' => $cronConfig->getReadableName() ?? $cronModule->getServiceId(),
                'cronTimeoutSecs' => $cronConfig->getTimeoutIteratedCronSec(),
            ],
        );
    }
}
