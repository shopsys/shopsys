<?php

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ZboziProductDomainFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainRepository
     */
    protected $zboziProductDomainRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    public function __construct(
        EntityManagerInterface $em,
        ZboziProductDomainRepository $zboziProductDomainRepository,
        ProductRepository $productRepository
    ) {
        $this->em = $em;
        $this->zboziProductDomainRepository = $zboziProductDomainRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain[]|null
     */
    public function findByProductId(int $productId): ?array
    {
        return $this->zboziProductDomainRepository->findByProductId($productId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain[]
     */
    public function getZboziProductDomainsByProductsAndDomainIndexedByProductId(array $products, DomainConfig $domain): array
    {
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        return $this->zboziProductDomainRepository->getZboziProductDomainsByProductsIdsDomainIdIndexedByProductId(
            $productIds,
            $domain->getId()
        );
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData[] $zboziProductDomainsData
     */
    public function saveZboziProductDomainsForProductId($productId, array $zboziProductDomainsData): void
    {
        $existingZboziProductDomains = $this->zboziProductDomainRepository->findByProductId($productId);

        $this->removeOldZboziProductDomainsForProductId($existingZboziProductDomains, $zboziProductDomainsData);

        foreach ($zboziProductDomainsData as $zboziProductDomainData) {
            $this->saveZboziProductDomain($productId, $zboziProductDomainData);
        }
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain[] $existingZboziProductDomains
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData[] $newZboziProductDomainsData
     */
    protected function removeOldZboziProductDomainsForProductId(
        array $existingZboziProductDomains,
        array $newZboziProductDomainsData
    ): void {
        $domainsIdsWithNewZboziProductDomains = [];
        foreach ($newZboziProductDomainsData as $newZboziProductDomainData) {
            $domainsIdsWithNewZboziProductDomains[$newZboziProductDomainData->domainId] = $newZboziProductDomainData->domainId;
        }

        foreach ($existingZboziProductDomains as $existingZboziProductDomain) {
            if (!array_key_exists($existingZboziProductDomain->getDomainId(), $domainsIdsWithNewZboziProductDomains)) {
                $this->em->remove($existingZboziProductDomain);
            }
        }
    }

    public function saveZboziProductDomain($productId, ZboziProductDomainData $zboziProductDomainData): void
    {
        $product = $this->productRepository->getById($productId);
        $zboziProductDomainData->product = $product;

        $existingZboziProductDomain = $this->zboziProductDomainRepository->findByProductIdAndDomainId(
            $productId,
            $zboziProductDomainData->domainId
        );

        if ($existingZboziProductDomain !== null) {
            $existingZboziProductDomain->edit($zboziProductDomainData);
        } else {
            $newZboziProductDomain = new ZboziProductDomain($zboziProductDomainData);
            $this->em->persist($newZboziProductDomain);
        }

        $this->em->flush();
    }

    public function delete($productId): void
    {
        $zboziProductDomains = $this->zboziProductDomainRepository->findByProductId($productId);

        foreach ($zboziProductDomains as $zboziProductDomain) {
            $this->em->remove($zboziProductDomain);
        }
        $this->em->flush();
    }
}
