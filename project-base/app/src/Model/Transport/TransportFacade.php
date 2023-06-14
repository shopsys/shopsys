<?php

declare(strict_types=1);

namespace App\Model\Transport;

use App\Model\Payment\PaymentFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade as BaseTransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;
use Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation;

/**
 * @property \App\Model\Transport\TransportRepository $transportRepository
 * @property \App\Model\Payment\PaymentRepository $paymentRepository
 * @property \App\Component\Image\ImageFacade $imageFacade
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
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Transport\TransportRepository $transportRepository
     * @param \App\Model\Payment\PaymentRepository $paymentRepository
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation $transportVisibilityCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFactory $transportFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactory $transportPriceFactory
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        TransportRepository $transportRepository,
        PaymentRepository $paymentRepository,
        TransportVisibilityCalculation $transportVisibilityCalculation,
        Domain $domain,
        ImageFacade $imageFacade,
        CurrencyFacade $currencyFacade,
        TransportPriceCalculation $transportPriceCalculation,
        TransportFactoryInterface $transportFactory,
        TransportPriceFactoryInterface $transportPriceFactory,
        private PaymentFacade $paymentFacade,
    ) {
        parent::__construct(
            $em,
            $transportRepository,
            $paymentRepository,
            $transportVisibilityCalculation,
            $domain,
            $imageFacade,
            $currencyFacade,
            $transportPriceCalculation,
            $transportFactory,
            $transportPriceFactory,
        );
    }

    /**
     * @param int|null $totalWeight
     * @return \App\Model\Transport\Transport[]
     */
    public function getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations(?int $totalWeight = null): array
    {
        $domainId = $this->domain->getId();
        $transports = $this->transportRepository->getAllWithEagerLoadedDomainsAndTranslations($this->domain->getCurrentDomainConfig(), $totalWeight);

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();
        /** @var \App\Model\Transport\Transport[] $filteredTransports */
        $filteredTransports = $this->transportVisibilityCalculation->filterVisible($transports, $visiblePayments, $domainId);

        return $filteredTransports;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @return bool
     */
    public function isTransportVisibleAndEnabledOnCurrentDomain(Transport $transport): bool
    {
        try {
            $this->getEnabledOnDomainByUuid($transport->getUuid(), $this->domain->getId());
        } catch (TransportNotFoundException $exception) {
            return false;
        }

        return true;
    }
}
