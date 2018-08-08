<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class TransportFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportRepository
     */
    protected $transportRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation
     */
    protected $transportVisibilityCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    protected $transportPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface
     */
    protected $transportFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface
     */
    protected $transportPriceFactory;

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
        TransportPriceFactoryInterface $transportPriceFactory
    ) {
        $this->em = $em;
        $this->transportRepository = $transportRepository;
        $this->paymentRepository = $paymentRepository;
        $this->transportVisibilityCalculation = $transportVisibilityCalculation;
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
        $this->currencyFacade = $currencyFacade;
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->transportFactory = $transportFactory;
        $this->transportPriceFactory = $transportPriceFactory;
    }

    public function create(TransportData $transportData): \Shopsys\FrameworkBundle\Model\Transport\Transport
    {
        $transport = $this->transportFactory->create($transportData);
        $this->em->persist($transport);
        $this->em->flush();
        $this->updateTransportPrices($transport, $transportData->pricesByCurrencyId);
        $this->imageFacade->uploadImage($transport, $transportData->image->uploadedFiles, null);
        $transport->setPayments($transportData->payments);
        $this->em->flush();

        return $transport;
    }

    public function edit(Transport $transport, TransportData $transportData): void
    {
        $transport->edit($transportData);
        $this->updateTransportPrices($transport, $transportData->pricesByCurrencyId);
        $this->imageFacade->uploadImage($transport, $transportData->image->uploadedFiles, null);
        $transport->setPayments($transportData->payments);
        $this->em->flush();
    }
    
    public function getById(int $id): \Shopsys\FrameworkBundle\Model\Transport\Transport
    {
        return $this->transportRepository->getById($id);
    }
    
    public function deleteById(int $id): void
    {
        $transport = $this->getById($id);
        $transport->markAsDeleted();
        $paymentsByTransport = $this->paymentRepository->getAllByTransport($transport);
        foreach ($paymentsByTransport as $payment) {
            $payment->getTransports()->removeElement($transport);
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $visiblePayments
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getVisibleOnCurrentDomain(array $visiblePayments): array
    {
        return $this->getVisibleByDomainId($this->domain->getId(), $visiblePayments);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $visiblePaymentsOnDomain
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getVisibleByDomainId(int $domainId, $visiblePaymentsOnDomain): array
    {
        $transports = $this->transportRepository->getAllByDomainId($domainId);

        return $this->transportVisibilityCalculation->filterVisible($transports, $visiblePaymentsOnDomain, $domainId);
    }

    /**
     * @param string[] $pricesByCurrencyId
     */
    protected function updateTransportPrices(Transport $transport, $pricesByCurrencyId): void
    {
        foreach ($this->currencyFacade->getAll() as $currency) {
            $price = $pricesByCurrencyId[$currency->getId()];
            $transport->setPrice($this->transportPriceFactory, $currency, $price);
        }
    }

    public function getAll()
    {
        return $this->transportRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getAllIncludingDeleted(): array
    {
        return $this->transportRepository->getAllIncludingDeleted();
    }

    /**
     * @return string[]
     */
    public function getTransportPricesWithVatIndexedByTransportId(Currency $currency): array
    {
        $transportPricesWithVatByTransportId = [];
        $transports = $this->getAllIncludingDeleted();
        foreach ($transports as $transport) {
            $transportPrice = $this->transportPriceCalculation->calculateIndependentPrice($transport, $currency);
            $transportPricesWithVatByTransportId[$transport->getId()] = $transportPrice->getPriceWithVat();
        }

        return $transportPricesWithVatByTransportId;
    }

    /**
     * @return string[]
     */
    public function getTransportVatPercentsIndexedByTransportId(): array
    {
        $transportVatPercentsByTransportId = [];
        $transports = $this->getAllIncludingDeleted();
        foreach ($transports as $transport) {
            $transportVatPercentsByTransportId[$transport->getId()] = $transport->getVat()->getPercent();
        }

        return $transportVatPercentsByTransportId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getIndependentBasePricesIndexedByCurrencyId(Transport $transport): array
    {
        $prices = [];
        foreach ($transport->getPrices() as $transportInputPrice) {
            $currency = $transportInputPrice->getCurrency();
            $prices[$currency->getId()] = $this->transportPriceCalculation->calculateIndependentPrice($transport, $currency);
        }

        return $prices;
    }
}
