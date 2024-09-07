<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;

class TransportFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportRepository $transportRepository
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository $paymentRepository
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation $transportVisibilityCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface $transportFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactory $transportPriceFactory
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly TransportRepository $transportRepository,
        protected readonly PaymentRepository $paymentRepository,
        protected readonly TransportVisibilityCalculation $transportVisibilityCalculation,
        protected readonly Domain $domain,
        protected readonly ImageFacade $imageFacade,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly TransportFactoryInterface $transportFactory,
        protected readonly TransportPriceFactory $transportPriceFactory,
        protected readonly PaymentFacade $paymentFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function create(TransportData $transportData)
    {
        $transport = $this->transportFactory->create($transportData);
        $this->em->persist($transport);
        $this->em->flush();
        $this->updateTransportPrices($transport, $transportData->inputPricesByDomain);
        $this->imageFacade->manageImages($transport, $transportData->image);
        $transport->setPayments($transportData->payments);
        $this->em->flush();

        return $transport;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     */
    public function edit(Transport $transport, TransportData $transportData)
    {
        $transport->edit($transportData);
        $this->updateTransportPrices($transport, $transportData->inputPricesByDomain);
        $this->imageFacade->manageImages($transport, $transportData->image);
        $transport->setPayments($transportData->payments);
        $this->em->flush();
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function getById($id)
    {
        return $this->transportRepository->getById($id);
    }

    /**
     * @param int $id
     */
    public function deleteById($id)
    {
        $transport = $this->getById($id);
        $transport->markAsDeleted();
        $paymentsByTransport = $this->paymentRepository->getAllByTransport($transport);

        foreach ($paymentsByTransport as $payment) {
            $payment->removeTransport($transport);
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $visiblePayments
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getVisibleOnCurrentDomain(array $visiblePayments)
    {
        return $this->getVisibleByDomainId($this->domain->getId(), $visiblePayments);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $visiblePaymentsOnDomain
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getVisibleByDomainId($domainId, $visiblePaymentsOnDomain)
    {
        $transports = $this->transportRepository->getAllByDomainId($domainId);

        return $this->transportVisibilityCalculation->filterVisible($transports, $visiblePaymentsOnDomain, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesData[] $inputPricesDataIndexedByDomainId
     */
    protected function updateTransportPrices(Transport $transport, array $inputPricesDataIndexedByDomainId): void
    {
        $this->deleteAllPricesByTransport($transport);

        $prices = [];

        foreach ($inputPricesDataIndexedByDomainId as $domainId => $pricesData) {
            foreach ($pricesData->pricesWithLimits as $pricesWithLimitData) {
                $prices[] = $this->transportPriceFactory->create($transport, $pricesWithLimitData->price, $domainId, $pricesWithLimitData->maxWeight);
            }
        }

        $transport->setPrices($prices);
        $this->em->flush();
    }

    public function getAll()
    {
        return $this->transportRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getAllIncludingDeleted()
    {
        return $this->transportRepository->getAllIncludingDeleted();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money[]
     */
    public function getTransportPricesWithVatByCurrencyAndDomainIdIndexedByTransportId(
        int $domainId,
    ): array {
        $transportPricesWithVatByTransportId = [];
        $transports = $this->getAllIncludingDeleted();

        foreach ($transports as $transport) {
            $transportInputPrice = $transport->getLowestPriceOnDomain($domainId);
            $transportPrice = $this->transportPriceCalculation->calculateIndependentPrice(
                $transportInputPrice,
            );
            $transportPricesWithVatByTransportId[$transport->getId()] = $transportPrice->getPriceWithVat();
        }

        return $transportPricesWithVatByTransportId;
    }

    /**
     * @param int $domainId
     * @return string[]
     */
    public function getTransportVatPercentsByDomainIdIndexedByTransportId(int $domainId): array
    {
        $transportVatPercentsByTransportId = [];
        $transports = $this->getAllIncludingDeleted();

        foreach ($transports as $transport) {
            $transportVatPercentsByTransportId[$transport->getId()] = $transport->getTransportDomain(
                $domainId,
            )->getVat()->getPercent();
        }

        return $transportVatPercentsByTransportId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[][]
     */
    public function getIndependentBasePricesIndexedByDomainId(Transport $transport): array
    {
        $prices = [];

        foreach ($transport->getPrices() as $transportInputPrice) {
            $domainId = $transportInputPrice->getDomainId();
            $prices[$domainId][] = $this->transportPriceCalculation->calculateIndependentPrice(
                $transportInputPrice,
            );
        }

        return $prices;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getPricesIndexedByTransportPriceId(Transport $transport): array
    {
        $prices = [];

        foreach ($transport->getPrices() as $transportPrice) {
            $prices[$transportPrice->getId()] = $this->transportPriceCalculation->calculateIndependentPrice(
                $transportPrice,
            );
        }

        return $prices;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function getByUuid(string $uuid): Transport
    {
        return $this->transportRepository->getOneByUuid($uuid);
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function getEnabledOnDomainByUuid(string $uuid, int $domainId): Transport
    {
        return $this->transportRepository->getEnabledOnDomainByUuid($uuid, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return bool
     */
    public function isTransportVisibleAndEnabledOnCurrentDomain(Transport $transport): bool
    {
        try {
            $this->getEnabledOnDomainByUuid($transport->getUuid(), $this->domain->getId());
        } catch (TransportNotFoundException) {
            return false;
        }

        return true;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart|null $cart
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations(?Cart $cart = null): array
    {
        $domainId = $this->domain->getId();
        $transports = $this->transportRepository->getAllWithEagerLoadedDomainsAndTranslations($this->domain->getCurrentDomainConfig(), $cart?->getTotalWeight());

        if ($cart !== null) {
            $transports = $this->transportVisibilityCalculation->filterTransportsByProductsInCart($transports, $cart);
        }

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();

        return $this->transportVisibilityCalculation->filterVisible($transports, $visiblePayments, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     */
    protected function deleteAllPricesByTransport(Transport $transport): void
    {
        $this->transportRepository->deleteAllPricesByTransport($transport);
        $transport->setPrices([]);
        $this->em->flush();
    }
}
