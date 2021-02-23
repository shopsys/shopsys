<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\TransportFacade as BaseTransportFacade;

/**
 * @property \App\Model\Payment\PaymentRepository $paymentRepository
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \App\Model\Transport\TransportPriceCalculation $transportPriceCalculation
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Transport\TransportRepository $transportRepository, \App\Model\Payment\PaymentRepository $paymentRepository, \Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation $transportVisibilityCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \App\Model\Transport\TransportPriceCalculation $transportPriceCalculation, \Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface $transportFactory, \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface $transportPriceFactory)
 * @method \App\Model\Transport\Transport create(\App\Model\Transport\TransportData $transportData)
 * @method edit(\App\Model\Transport\Transport $transport, \App\Model\Transport\TransportData $transportData)
 * @method \App\Model\Transport\Transport getById(int $id)
 * @method \App\Model\Transport\Transport[] getVisibleOnCurrentDomain(\App\Model\Payment\Payment[] $visiblePayments)
 * @method \App\Model\Transport\Transport[] getVisibleByDomainId(int $domainId, \App\Model\Payment\Payment[] $visiblePaymentsOnDomain)
 * @method updateTransportPrices(\App\Model\Transport\Transport $transport, \Shopsys\FrameworkBundle\Component\Money\Money[] $pricesIndexedByDomainId)
 * @method \App\Model\Transport\Transport[] getAllIncludingDeleted()
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] getIndependentBasePricesIndexedByDomainId(\App\Model\Transport\Transport $transport)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] getPricesIndexedByDomainId(\App\Model\Transport\Transport|null $transport)
 * @method \App\Model\Transport\Transport getByUuid(string $uuid)
 */
class TransportFacade extends BaseTransportFacade
{
}
