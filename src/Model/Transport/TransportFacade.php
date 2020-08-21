<?php

declare(strict_types=1);

namespace App\Model\Transport;

use App\Model\Transport\TransportPackage\TransportPackageFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade as BaseTransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;
use Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation;

/**
 * @property \App\Model\Payment\PaymentRepository $paymentRepository
 * @property \App\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \App\Model\Transport\TransportPriceCalculation $transportPriceCalculation
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
    /**
     * @var \App\Model\Transport\TransportPackage\TransportPackageFacade
     */
    private TransportPackageFacade $transportPackageFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportRepository $transportRepository
     * @param \App\Model\Payment\PaymentRepository $paymentRepository
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation $transportVisibilityCalculation
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface $transportFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface $transportPriceFactory
     * @param \App\Model\Transport\TransportPackage\TransportPackageFacade $transportPackageFacade
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
        TransportPackageFacade $transportPackageFacade
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
            $transportPriceFactory
        );
        $this->transportPackageFacade = $transportPackageFacade;
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     * @return \App\Model\Transport\Transport
     */
    public function create(TransportData $transportData)
    {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = parent::create($transportData);
        $this->transportPackageFacade->updateTransportPackages($transport, $transportData);

        return $transport;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Transport\TransportData $transportData
     */
    public function edit(BaseTransport $transport, TransportData $transportData)
    {
        parent::edit($transport, $transportData);
        $this->transportPackageFacade->updateTransportPackages($transport, $transportData);
    }
}
