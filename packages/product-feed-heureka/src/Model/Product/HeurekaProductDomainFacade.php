<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class HeurekaProductDomainFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainRepository
     */
    protected $heurekaProductDomainRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    public function __construct(
        EntityManagerInterface $em,
        HeurekaProductDomainRepository $heurekaProductDomainRepository,
        ProductRepository $productRepository
    ) {
        $this->em = $em;
        $this->heurekaProductDomainRepository = $heurekaProductDomainRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[]|null
     */
    public function findByProductId(int $productId): ?array
    {
        return $this->heurekaProductDomainRepository->findByProductId($productId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[]
     */
    public function getHeurekaProductDomainsByProductsAndDomainIndexedByProductId(array $products, DomainConfig $domain): array
    {
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        return $this->heurekaProductDomainRepository->getHeurekaProductDomainsByProductsIdsDomainIdIndexedByProductId(
            $productIds,
            $domain->getId()
        );
    }

    public function delete($productId): void
    {
        $heurekaProductDomains = $this->heurekaProductDomainRepository->findByProductId($productId);

        foreach ($heurekaProductDomains as $heurekaProductDomain) {
            $this->em->remove($heurekaProductDomain);
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainData[] $heurekaProductDomainsData
     */
    public function saveHeurekaProductDomainsForProductId($productId, array $heurekaProductDomainsData): void
    {
        $existingHeurekaProductDomains = $this->heurekaProductDomainRepository->findByProductId($productId);

        $this->removeOldHeurekaProductDomainsForProductId($existingHeurekaProductDomains, $heurekaProductDomainsData);

        foreach ($heurekaProductDomainsData as $heurekaProductDomainData) {
            $this->saveHeurekaProductDomain($productId, $heurekaProductDomainData);
        }
    }

    public function saveHeurekaProductDomain($productId, HeurekaProductDomainData $heurekaProductDomainData): void
    {
        $product = $this->productRepository->getById($productId);
        $heurekaProductDomainData->product = $product;

        $existingHeurekaProductDomain = $this->heurekaProductDomainRepository->findByProductIdAndDomainId(
            $productId,
            $heurekaProductDomainData->domainId
        );

        if ($existingHeurekaProductDomain !== null) {
            $existingHeurekaProductDomain->edit($heurekaProductDomainData);
        } else {
            $newHeurekaProductDomain = new HeurekaProductDomain($heurekaProductDomainData);
            $this->em->persist($newHeurekaProductDomain);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[] $existingHeurekaProductDomains
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainData[] $newHeurekaProductDomainsData
     */
    protected function removeOldHeurekaProductDomainsForProductId(
        array $existingHeurekaProductDomains,
        array $newHeurekaProductDomainsData
    ): void {
        $domainsIdsWithNewHeurekaProductDomains = [];
        foreach ($newHeurekaProductDomainsData as $newHeurekaProductDomainData) {
            $domainsIdsWithNewHeurekaProductDomains[$newHeurekaProductDomainData->domainId] = $newHeurekaProductDomainData->domainId;
        }

        foreach ($existingHeurekaProductDomains as $existingHeurekaProductDomain) {
            if (!array_key_exists($existingHeurekaProductDomain->getDomainId(), $domainsIdsWithNewHeurekaProductDomains)) {
                $this->em->remove($existingHeurekaProductDomain);
            }
        }
    }
}
