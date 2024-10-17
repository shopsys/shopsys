<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\DefaultController as BaseDefaultController;

/**
 * @property \App\Component\Setting\Setting $setting
 * @property \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
 * @property \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
 * @method \App\Model\Administrator\Administrator getCurrentAdministrator()
 * @property \App\Model\Product\Parameter\ParameterFacade $parameterFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Statistics\StatisticsFacade $statisticsFacade, \Shopsys\FrameworkBundle\Model\Statistics\StatisticsProcessingFacade $statisticsProcessingFacade, \App\Model\Mail\MailTemplateFacade $mailTemplateFacade, \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade, \App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade $cronModuleFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig $cronConfig, \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension, \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Product\Parameter\ParameterFacade $parameterFacade)
 */
class DefaultController extends BaseDefaultController
{
}
