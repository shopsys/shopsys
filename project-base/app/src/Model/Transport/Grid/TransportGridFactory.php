<?php

declare(strict_types=1);

namespace App\Model\Transport\Grid;

use Shopsys\FrameworkBundle\Model\Transport\Grid\TransportGridFactory as BaseTransportGridFactory;

/**
 * @property \App\Model\Transport\TransportRepository $transportRepository
 * @property \App\Model\Transport\TransportFacade $transportFacade
 * @method __construct(\Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \App\Model\Transport\TransportRepository $transportRepository, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \App\Model\Transport\TransportFacade $transportFacade, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] getDisplayPrices(\App\Model\Transport\Transport $transport)
 */
class TransportGridFactory extends BaseTransportGridFactory
{
}
