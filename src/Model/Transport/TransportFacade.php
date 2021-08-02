<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\TransportFacade as BaseTransportFacade;

/**
 * @property \App\Model\Transport\TransportRepository $transportRepository
 * @property \App\Model\Payment\PaymentRepository $paymentRepository
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Transport\TransportRepository $transportRepository, \App\Model\Payment\PaymentRepository $paymentRepository, \Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation $transportVisibilityCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation, \Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface $transportFactory, \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface $transportPriceFactory)
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
 * @method \App\Model\Transport\Transport getEnabledOnDomainByUuid(string $uuid, int $domainId)
 */
class TransportFacade extends BaseTransportFacade
{
    /**
     * @param \App\Model\Payment\Payment[] $visiblePayments
     * @param int $totalWeight
     * @return \App\Model\Transport\Transport[]
     */
    public function getVisibleOnCurrentDomainAcceptingWeight(array $visiblePayments, int $totalWeight): array
    {
        $domainId = $this->domain->getId();
        $transports = $this->transportRepository->getAllByDomainIdAndAcceptingWeight($domainId, $totalWeight);

        /** @var \App\Model\Transport\Transport[] $filteredTransports */
        $filteredTransports = $this->transportVisibilityCalculation->filterVisible($transports, $visiblePayments, $domainId);
        return $filteredTransports;
    }
}
