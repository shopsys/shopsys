<?php

declare(strict_types=1);

namespace App\Model\Transport\Grid;

use Shopsys\FrameworkBundle\Model\Transport\Grid\TransportGridFactory as BaseTransportGridFactory;

/**
 * @property \App\Model\Transport\TransportRepository $transportRepository
 * @property \App\Model\Transport\TransportFacade $transportFacade
 * @method __construct(\Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \App\Model\Transport\TransportRepository $transportRepository, \App\Model\Transport\TransportFacade $transportFacade, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[] getDisplayPrices(\App\Model\Transport\Transport $transport)
 */
class TransportGridFactory extends BaseTransportGridFactory
{
}
