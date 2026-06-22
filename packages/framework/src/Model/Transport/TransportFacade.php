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
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly TransportRepository $transportRepository,
        protected readonly PaymentRepository $paymentRepository,
        protected readonly TransportVisibilityCalculation $transportVisibilityCalculation,
        protected readonly Domain $domain,
        protected readonly ImageFacade $imageFacade,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly TransportFactory $transportFactory,
        protected readonly TransportPriceFactory $transportPriceFactory,
        protected readonly PaymentFacade $paymentFacade,
    ) {
    }

    public function create(TransportData $transportData): Transport
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

    public function edit(Transport $transport, TransportData $transportData): void
    {
        $transport->edit($transportData);
        $this->updateTransportPrices($transport, $transportData->inputPricesByDomain);
        $this->imageFacade->manageImages($transport, $transportData->image);
        $transport->setPayments($transportData->payments);
        $this->em->flush();
    }

    public function getById(int $id): Transport
    {
        return $this->transportRepository->getById($id);
    }

    public function deleteById(int $id): void
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
    public function getVisibleOnCurrentDomain(array $visiblePayments): array
    {
        return $this->getVisibleByDomainId($this->domain->getId(), $visiblePayments);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $visiblePaymentsOnDomain
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getVisibleByDomainId(int $domainId, array $visiblePaymentsOnDomain): array
    {
        $transports = $this->transportRepository->getAllByDomainId($domainId);

        return $this->transportVisibilityCalculation->filterVisible($transports, $visiblePaymentsOnDomain, $domainId);
    }

    /**
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getAll(): array
    {
        return $this->transportRepository->getAll();
    }

    /**
     * @return int[]
     */
    public function getDomainIdsWithAnyEnabledTransport(): array
    {
        return $this->transportRepository->getDomainIdsWithAnyEnabledTransport();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getAllIncludingDeleted(): array
    {
        return $this->transportRepository->getAllIncludingDeleted();
    }

    /**
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[][]
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[]
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

    public function getByUuid(string $uuid): Transport
    {
        return $this->transportRepository->getOneByUuid($uuid);
    }

    public function getEnabledOnDomainByUuid(string $uuid, int $domainId): Transport
    {
        return $this->transportRepository->getEnabledOnDomainByUuid($uuid, $domainId);
    }

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
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations(?Cart $cart = null): array
    {
        $domainId = $this->domain->getId();
        $transports = $this->transportRepository->getAllWithEagerLoadedDomainsAndTranslations($this->domain->getCurrentDomainConfig(), $cart?->getTotalWeight());

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();

        return $this->transportVisibilityCalculation->filterVisible($transports, $visiblePayments, $domainId);
    }

    protected function deleteAllPricesByTransport(Transport $transport): void
    {
        $this->transportRepository->deleteAllPricesByTransport($transport);
        $transport->setPrices([]);
        $this->em->flush();
    }
}
