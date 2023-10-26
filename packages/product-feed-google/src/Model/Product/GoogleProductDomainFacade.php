<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class GoogleProductDomainFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainRepository $googleProductDomainRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GoogleProductDomainRepository $googleProductDomainRepository,
        protected readonly ProductRepository $productRepository,
    ) {
    }

    /**
     * @param int $productId
     * @return \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomain[]|null
     */
    public function findByProductId($productId)
    {
        return $this->googleProductDomainRepository->findByProductId($productId);
    }

    /**
     * @param int $productId
     * @param \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainData[] $googleProductDomainsData
     */
    public function saveGoogleProductDomainsForProductId($productId, array $googleProductDomainsData)
    {
        $existingGoogleProductDomains = $this->googleProductDomainRepository->findByProductId($productId);

        $this->removeOldGoogleProductDomains($existingGoogleProductDomains, $googleProductDomainsData);

        foreach ($googleProductDomainsData as $googleProductDomainData) {
            $this->saveGoogleProductDomain($productId, $googleProductDomainData);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomain[] $existingGoogleProductDomains
     * @param \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainData[] $newGoogleProductDomainsData
     */
    protected function removeOldGoogleProductDomains(
        array $existingGoogleProductDomains,
        array $newGoogleProductDomainsData,
    ) {
        $domainsIdsWithNewGoogleProductDomains = [];

        foreach ($newGoogleProductDomainsData as $newGoogleProductDomainData) {
            $domainsIdsWithNewGoogleProductDomains[$newGoogleProductDomainData->domainId] = $newGoogleProductDomainData->domainId;
        }

        foreach ($existingGoogleProductDomains as $existingGoogleProductDomain) {
            $domainIdOfExistingGoogleProductDomain = $existingGoogleProductDomain->getDomainId();

            if (!isset($domainsIdsWithNewGoogleProductDomains[$domainIdOfExistingGoogleProductDomain])) {
                $this->em->remove($existingGoogleProductDomain);
            }
        }
    }

    /**
     * @param int $productId
     * @param \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainData $googleProductDomainData
     */
    protected function saveGoogleProductDomain($productId, GoogleProductDomainData $googleProductDomainData)
    {
        $product = $this->productRepository->getById($productId);
        $googleProductDomainData->product = $product;

        $existingGoogleProductDomain = $this->googleProductDomainRepository->findByProductIdAndDomainId(
            $productId,
            $googleProductDomainData->domainId,
        );

        if ($existingGoogleProductDomain !== null) {
            $existingGoogleProductDomain->edit($googleProductDomainData);
        } else {
            $newGoogleProductDomain = new GoogleProductDomain($googleProductDomainData);
            $this->em->persist($newGoogleProductDomain);
        }
    }

    /**
     * @param int $productId
     */
    public function delete($productId)
    {
        $googleProductDomains = $this->googleProductDomainRepository->findByProductId($productId);

        foreach ($googleProductDomains as $googleProductDomain) {
            $this->em->remove($googleProductDomain);
        }
        $this->em->flush();
    }
}
