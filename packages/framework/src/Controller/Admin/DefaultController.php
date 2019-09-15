<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Statistics\StatisticsFacade;
use Shopsys\FrameworkBundle\Model\Statistics\StatisticsProcessingFacade;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AdminBaseController
{
    protected const PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR = 7;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Statistics\StatisticsFacade
     */
    protected $statisticsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Statistics\StatisticsProcessingFacade
     */
    protected $statisticsProcessingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade
     */
    protected $mailTemplateFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade
     */
    protected $unitFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade
     */
    protected $availabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade
     */
    protected $cronModuleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    protected $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig
     */
    protected $cronConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronFacade
     */
    protected $cronFacade;

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
     */
    public function __construct(
        StatisticsFacade $statisticsFacade,
        StatisticsProcessingFacade $statisticsProcessingFacade,
        MailTemplateFacade $mailTemplateFacade,
        UnitFacade $unitFacade,
        Setting $setting,
        AvailabilityFacade $availabilityFacade,
        CronModuleFacade $cronModuleFacade,
        GridFactory $gridFactory,
        CronConfig $cronConfig,
        CronFacade $cronFacade
    ) {
        $this->statisticsFacade = $statisticsFacade;
        $this->statisticsProcessingFacade = $statisticsProcessingFacade;
        $this->mailTemplateFacade = $mailTemplateFacade;
        $this->unitFacade = $unitFacade;
        $this->setting = $setting;
        $this->availabilityFacade = $availabilityFacade;
        $this->cronModuleFacade = $cronModuleFacade;
        $this->gridFactory = $gridFactory;
        $this->cronConfig = $cronConfig;
        $this->cronFacade = $cronFacade;
    }

    /**
     * @Route("/dashboard/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dashboardAction(): Response
    {
        $registeredInLastTwoWeeks = $this->statisticsFacade->getCustomersRegistrationsCountByDayInLastTwoWeeks();
        $registeredInLastTwoWeeksDates = $this->statisticsProcessingFacade->getDateTimesFormattedToLocaleFormat($registeredInLastTwoWeeks);
        $registeredInLastTwoWeeksCounts = $this->statisticsProcessingFacade->getCounts($registeredInLastTwoWeeks);
        $newOrdersCountByDayInLastTwoWeeks = $this->statisticsFacade->getNewOrdersCountByDayInLastTwoWeeks();
        $newOrdersInLastTwoWeeksDates = $this->statisticsProcessingFacade->getDateTimesFormattedToLocaleFormat($newOrdersCountByDayInLastTwoWeeks);
        $newOrdersInLastTwoWeeksCounts = $this->statisticsProcessingFacade->getCounts($newOrdersCountByDayInLastTwoWeeks);

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

        $currentCountOfNewCustomers = $this->statisticsFacade->getNewCustomersCount(static::PREVIOUS_DAYS_TO_LOAD_STATISTICS_FOR);
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
            $this->getFlashMessageSender()->addErrorFlashTwig(
                t('<a href="{{ url }}">Some required e-mail templates are not fully set.</a>'),
                [
                    'url' => $this->generateUrl('admin_mail_template'),
                ]
            );
        }

        if (empty($this->unitFacade->getAll())) {
            $this->getFlashMessageSender()->addErrorFlashTwig(
                t('<a href="{{ url }}">There are no units, you need to create some.</a>'),
                [
                    'url' => $this->generateUrl('admin_unit_list'),
                ]
            );
        }

        if ($this->setting->get(Setting::DEFAULT_UNIT) === 0) {
            $this->getFlashMessageSender()->addErrorFlashTwig(
                t('<a href="{{ url }}">Default unit is not set.</a>'),
                [
                    'url' => $this->generateUrl('admin_unit_list'),
                ]
            );
        }

        if (empty($this->availabilityFacade->getAll())) {
            $this->getFlashMessageSender()->addErrorFlashTwig(
                t('<a href="{{ url }}">There are no availabilities, you need to create some.</a>'),
                [
                    'url' => $this->generateUrl('admin_availability_list'),
                ]
            );
        }

        if ($this->setting->get(Setting::DEFAULT_AVAILABILITY_IN_STOCK) === 0) {
            $this->getFlashMessageSender()->addErrorFlashTwig(
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
        if ($this->isGranted(Roles::ROLE_SUPER_ADMIN) === false) {
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

        foreach ($cronConfigs as $cronConfig) {
            if (array_key_exists($cronConfig->getServiceId(), $cronModules) === false) {
                $cronModule = $this->cronModuleFacade->getCronModuleByServiceId($cronConfig->getServiceId());
            } else {
                $cronModule = $cronModules[$cronConfig->getServiceId()];
            }

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
            ];
        }

        $dataSource = new ArrayDataSource($data);

        $cronListGrid = $this->gridFactory->create('cronList', $dataSource);

        $cronListGrid->addColumn('name', 'name', t('Name'), false);
        $cronListGrid->addColumn('readableFrequency', 'readableFrequency', t('Frequency'), false);
        $cronListGrid->addColumn('lastStartedAt', 'lastStartedAt', t('Last started at'), false);
        $cronListGrid->addColumn('lastFinishedAt', 'lastFinishedAt', t('Last finished at'), false);
        $cronListGrid->addColumn('lastDuration', 'lastDuration', t('Last duration (sec)'), false)->setClassAttribute('table-col table-col-10');
        $cronListGrid->addColumn('status', 'status', t('Status'), false)->setClassAttribute('table-col table-col-10');

        if ($this->isGranted(Roles::ROLE_SUPER_ADMIN)) {
            $cronListGrid->addColumn('actions', 'actions', t('Action'))->setClassAttribute('column--superadmin');
        }

        $cronListGrid->setTheme('@ShopsysFramework/Admin/Content/Default/cronListGrid.html.twig');

        return $cronListGrid->createView();
    }

    /**
     * @Route("/cron-schedule/{serviceId}")
     * @param string $serviceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function scheduleCronAction(string $serviceId): Response
    {
        $this->cronModuleFacade->schedule($serviceId);
        $this->getFlashMessageSender()->addSuccessFlash(
            t('Cron with serviceID `%serviceId%` was scheduled', ['%serviceId%' => $serviceId])
        );

        return $this->redirectToRoute('admin_default_dashboard');
    }
}
