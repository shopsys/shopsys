<?php

declare(strict_types=1);

namespace App\Model\Transport\Logistic;

use App\Component\Domain\Domain;
use App\Model\Cart\CartFacade;
use App\Model\Product\Package\ProductPackageRepository;
use App\Model\Product\Package\ProductPackagesCollection;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportPackage\TransportPackage;
use App\Model\Transport\TransportPackage\TransportPackageFacade;

class TransportLogisticFacade
{
    /**
     * @var \App\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Product\Package\ProductPackageRepository
     */
    private ProductPackageRepository $productPackageRepository;

    /**
     * @var \App\Model\Transport\TransportPackage\TransportPackageFacade
     */
    private TransportPackageFacade $transportPackageFacade;

    /**
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\Package\ProductPackageRepository $productPackageRepository
     * @param \App\Model\Transport\TransportPackage\TransportPackageFacade $transportPackageFacade
     */
    public function __construct(
        CartFacade $cartFacade,
        Domain $domain,
        ProductPackageRepository $productPackageRepository,
        TransportPackageFacade $transportPackageFacade
    ) {
        $this->cartFacade = $cartFacade;
        $this->domain = $domain;
        $this->productPackageRepository = $productPackageRepository;
        $this->transportPackageFacade = $transportPackageFacade;
    }

    /**
     * @param \App\Model\Transport\Transport[] $transports
     * @return \App\Model\Transport\Transport[]
     */
    public function filterAllowedTransportsForCurrentCart(array $transports): array
    {
        $quantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();
        $domainId = $this->domain->getId();

        $productPackagesCollection = $this->productPackageRepository->getProductPackagesCollectionByQuantifiedProducts($quantifiedProducts);

        $packageTransports = $this->filterTransportsByType($transports, Transport::TYPE_PACKAGE);
        $possiblePackageTransports = [];
        if ($this->existsProductWhichIsNotSupportPackageTransport($quantifiedProducts, $domainId) === false) {
            foreach ($packageTransports as $packageTransport) {
                if ($this->isTransportPossibleByProductPackageCollection($packageTransport, $productPackagesCollection, $domainId) === true) {
                    $possiblePackageTransports[] = $packageTransport;
                }
            }
        }

        return $this->filterPossibleTransports($transports, $possiblePackageTransports);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Product\Package\ProductPackagesCollection $productPackagesCollection
     * @param int $domainId
     * @return bool
     */
    private function isTransportPossibleByProductPackageCollection(
        Transport $transport,
        ProductPackagesCollection $productPackagesCollection,
        int $domainId
    ): bool {
        return $this->findFirstPossibleTransportPackageByProductPackageCollection($transport, $productPackagesCollection, $domainId) !== null;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Product\Package\ProductPackagesCollection $productPackagesCollection
     * @param int $domainId
     * @return \App\Model\Transport\TransportPackage\TransportPackage|null
     */
    public function findFirstPossibleTransportPackageByProductPackageCollection(
        Transport $transport,
        ProductPackagesCollection $productPackagesCollection,
        int $domainId
    ): ?TransportPackage {
        $transportPackages = $this->transportPackageFacade->getTransportPackagesByTransportAndDomainId($transport, $domainId);

        foreach ($transportPackages as $transportPackage) {
            if ($transportPackage->getMaxWeight() >= $productPackagesCollection->getWeightSum()
                && ($transportPackage->getMaxProductPackagesCount() === null || $transportPackage->getMaxProductPackagesCount() >= $productPackagesCollection->getTotalPackagesCount())
                && ($transportPackage->getMaxGirth() === null || $transportPackage->getMaxGirth() >= $productPackagesCollection->getTotalGirth())
                && ($transportPackage->getDimension1() === null || $transportPackage->getDimension1() >= $productPackagesCollection->getTopDimension1())
                && ($transportPackage->getDimension2() === null || $transportPackage->getDimension2() >= $productPackagesCollection->getTopDimension2())
                && ($transportPackage->getDimension3() === null || $transportPackage->getDimension3() >= $productPackagesCollection->getDimension3Sum())
            ) {
                return $transportPackage;
            }
        }

        return null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @return bool
     */
    private function existsProductWhichIsNotSupportPackageTransport(array $quantifiedProducts, int $domainId): bool
    {
        foreach ($quantifiedProducts as $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
            $product = $quantifiedProduct->getProduct();
            if ($product->canBeShippedAsPackage($domainId) === false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \App\Model\Transport\Transport[] $transports
     * @param \App\Model\Transport\Transport[] $possiblePackageTransports
     * @return \App\Model\Transport\Transport[]
     */
    private function filterPossibleTransports(array $transports, array $possiblePackageTransports): array
    {
        $isAllowedPackageTransport = count($possiblePackageTransports) > 0;
        $isOverLimitTransport = $this->cartFacade->isCartContainsProductWithOverLimitQuantity();
        foreach ($transports as $key => $transport) {
            if ($isAllowedPackageTransport === true
                && $transport->getType() === Transport::TYPE_PALLET
            ) {
                unset($transports[$key]);
                continue;
            }

            if ($isAllowedPackageTransport === false && $transport->getType() === Transport::TYPE_PACKAGE) {
                unset($transports[$key]);
                continue;
            }

            if ($isAllowedPackageTransport === true
                && $transport->getType() === Transport::TYPE_PACKAGE
                && in_array($transport, $possiblePackageTransports, true) === false
            ) {
                unset($transports[$key]);
            }

            if ($transport->isOverLimitTransport() !== $isOverLimitTransport) {
                unset($transports[$key]);
            }
        }

        return $transports;
    }

    /**
     * @param \App\Model\Transport\Transport[] $transports
     * @param string $type
     * @return \App\Model\Transport\Transport[]
     */
    private function filterTransportsByType(array $transports, string $type): array
    {
        $filteredTransports = [];
        foreach ($transports as $transport) {
            if ($transport->getType() === $type) {
                $filteredTransports[] = $transport;
            }
        }

        return $filteredTransports;
    }
}
