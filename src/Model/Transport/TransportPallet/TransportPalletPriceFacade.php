<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPallet;

use App\Component\Pricing\PriceToAndPriceData;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportData;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class TransportPalletPriceFacade
{
    /**
     * @var \App\Model\Transport\TransportPallet\TransportPalletPriceRepository
     */
    private TransportPalletPriceRepository $transportPalletPriceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param \App\Model\Transport\TransportPallet\TransportPalletPriceRepository $transportPalletPriceRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        TransportPalletPriceRepository $transportPalletPriceRepository,
        Domain $domain,
        EntityManagerInterface $entityManager
    ) {
        $this->transportPalletPriceRepository = $transportPalletPriceRepository;
        $this->domain = $domain;
        $this->entityManager = $entityManager;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param int $domainId
     * @return \App\Model\Transport\TransportPallet\TransportPalletPrice[]
     */
    public function getSortedPalletPricesByTransportAndDomain(Transport $transport, int $domainId): array
    {
        return $this->transportPalletPriceRepository->getSortedPalletPricesByTransportAndDomain($transport, $domainId);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceByProductsPrice(Transport $transport, int $domainId, Price $productsPrice): Money
    {
        return $this->transportPalletPriceRepository->getPriceByProductsPrice($transport, $domainId, $productsPrice);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Transport\TransportData $transportData
     */
    public function updateTransportPalletPrices(Transport $transport, TransportData $transportData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $maxTransportPalletPrice = null;

            $oldPalletPrices = $this->transportPalletPriceRepository->getSortedPalletPricesByTransportAndDomain($transport, $domainId);
            if ($transport->getType() !== Transport::TYPE_PALLET || array_key_exists($domainId, $transportData->palletPricesByDomainId) === false) {
                $newPalletPricesData = [];
            } else {
                $newPalletPricesData = $transportData->palletPricesByDomainId[$domainId];
            }

            foreach ($oldPalletPrices as $oldPalletPrice) {
                $matchedPriceToAndPriceData = $this->findPriceDataWithSamePriceFrom($newPalletPricesData, $oldPalletPrice);
                if ($matchedPriceToAndPriceData === null || $oldPalletPrice->getProductsPriceTo() === null) {
                    $this->entityManager->remove($oldPalletPrice);
                } else {
                    $oldPalletPrice->updateTransportPrice($matchedPriceToAndPriceData->price);
                    $maxTransportPalletPrice = $this->getMaxTransportPalletPriceByPriceTo($oldPalletPrice, $maxTransportPalletPrice);
                }
            }

            foreach ($newPalletPricesData as $newPalletPriceData) {
                if ($this->containsSamePriceToInEntities($oldPalletPrices, $newPalletPriceData) === false) {
                    $palletPrice = new TransportPalletPrice($transport, $domainId, $newPalletPriceData->priceTo, $newPalletPriceData->price);
                    $this->entityManager->persist($palletPrice);

                    $maxTransportPalletPrice = $this->getMaxTransportPalletPriceByPriceTo($palletPrice, $maxTransportPalletPrice);
                }
            }

            if ($maxTransportPalletPrice !== null) {
                $maxTransportPalletPrice->setProductsPriceToAsNull();
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param \App\Component\Pricing\PriceToAndPriceData[] $priceToAndPricesData
     * @param \App\Model\Transport\TransportPallet\TransportPalletPrice $transportPalletPrice
     * @return \App\Component\Pricing\PriceToAndPriceData|null
     */
    private function findPriceDataWithSamePriceFrom(array $priceToAndPricesData, TransportPalletPrice $transportPalletPrice): ?PriceToAndPriceData
    {
        if ($transportPalletPrice->getProductsPriceTo() === null) {
            return null;
        }

        foreach ($priceToAndPricesData as $priceToAndPriceData) {
            if ($priceToAndPriceData->priceTo->equals($transportPalletPrice->getProductsPriceTo())) {
                return $priceToAndPriceData;
            }
        }

        return null;
    }

    /**
     * @param \App\Model\Transport\TransportPallet\TransportPalletPrice[] $transportPalletPrices
     * @param \App\Component\Pricing\PriceToAndPriceData $priceToAndPriceData
     * @return bool
     */
    private function containsSamePriceToInEntities(array $transportPalletPrices, PriceToAndPriceData $priceToAndPriceData): bool
    {
        foreach ($transportPalletPrices as $transportPalletPrice) {
            if ($transportPalletPrice->getProductsPriceTo() !== null
                && $transportPalletPrice->getProductsPriceTo()->equals($priceToAndPriceData->priceTo)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \App\Model\Transport\TransportPallet\TransportPalletPrice $transportPalletPrice1
     * @param \App\Model\Transport\TransportPallet\TransportPalletPrice|null $transportPalletPrice2
     * @return \App\Model\Transport\TransportPallet\TransportPalletPrice
     */
    private function getMaxTransportPalletPriceByPriceTo(
        TransportPalletPrice $transportPalletPrice1,
        ?TransportPalletPrice $transportPalletPrice2
    ): TransportPalletPrice {
        if ($transportPalletPrice2 === null
            || (
                $transportPalletPrice2->getProductsPriceTo() !== null
                && $transportPalletPrice2->getProductsPriceTo()->isLessThan(
                    $transportPalletPrice1->getProductsPriceTo()
                )
            )) {
            return $transportPalletPrice1;
        }

        return $transportPalletPrice2;
    }
}
