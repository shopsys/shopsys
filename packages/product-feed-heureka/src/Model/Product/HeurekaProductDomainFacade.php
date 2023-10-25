<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class HeurekaProductDomainFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainRepository $heurekaProductDomainRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly HeurekaProductDomainRepository $heurekaProductDomainRepository,
        protected readonly ProductRepository $productRepository,
    ) {
    }

    /**
     * @param int $productId
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[]|null
     */
    public function findByProductId($productId)
    {
        return $this->heurekaProductDomainRepository->findByProductId($productId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domain
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[]
     */
    public function getHeurekaProductDomainsByProductsAndDomainIndexedByProductId(
        array $products,
        DomainConfig $domain,
    ) {
        $productIds = [];

        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        return $this->heurekaProductDomainRepository->getHeurekaProductDomainsByProductsIdsDomainIdIndexedByProductId(
            $productIds,
            $domain->getId(),
        );
    }

    /**
     * @param int $productId
     */
    public function delete($productId)
    {
        $heurekaProductDomains = $this->heurekaProductDomainRepository->findByProductId($productId);

        foreach ($heurekaProductDomains as $heurekaProductDomain) {
            $this->em->remove($heurekaProductDomain);
        }
        $this->em->flush();
    }

    /**
     * @param int $productId
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainData[] $heurekaProductDomainsData
     */
    public function saveHeurekaProductDomainsForProductId($productId, array $heurekaProductDomainsData)
    {
        $existingHeurekaProductDomains = $this->heurekaProductDomainRepository->findByProductId($productId);

        $this->removeOldHeurekaProductDomainsForProductId($existingHeurekaProductDomains, $heurekaProductDomainsData);

        foreach ($heurekaProductDomainsData as $heurekaProductDomainData) {
            $this->saveHeurekaProductDomain($productId, $heurekaProductDomainData);
        }

        $this->em->flush();
    }

    /**
     * @param int $productId
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainData $heurekaProductDomainData
     */
    protected function saveHeurekaProductDomain($productId, HeurekaProductDomainData $heurekaProductDomainData)
    {
        $product = $this->productRepository->getById($productId);
        $heurekaProductDomainData->product = $product;

        $existingHeurekaProductDomain = $this->heurekaProductDomainRepository->findByProductIdAndDomainId(
            $productId,
            $heurekaProductDomainData->domainId,
        );

        if ($existingHeurekaProductDomain !== null) {
            $existingHeurekaProductDomain->edit($heurekaProductDomainData);
        } else {
            $newHeurekaProductDomain = new HeurekaProductDomain($heurekaProductDomainData);
            $this->em->persist($newHeurekaProductDomain);
        }
    }

    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[] $existingHeurekaProductDomains
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainData[] $newHeurekaProductDomainsData
     */
    protected function removeOldHeurekaProductDomainsForProductId(
        array $existingHeurekaProductDomains,
        array $newHeurekaProductDomainsData,
    ) {
        $domainsIdsWithNewHeurekaProductDomains = [];

        foreach ($newHeurekaProductDomainsData as $newHeurekaProductDomainData) {
            $domainsIdsWithNewHeurekaProductDomains[$newHeurekaProductDomainData->domainId] = $newHeurekaProductDomainData->domainId;
        }

        foreach ($existingHeurekaProductDomains as $existingHeurekaProductDomain) {
            if (!array_key_exists(
                $existingHeurekaProductDomain->getDomainId(),
                $domainsIdsWithNewHeurekaProductDomains,
            )) {
                $this->em->remove($existingHeurekaProductDomain);
            }
        }
    }
}
