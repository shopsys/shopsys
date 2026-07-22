<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdditionalService\Exception\AdditionalServiceNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductAdditionalServiceDomain;

class AdditionalServiceRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getAdditionalServiceRepository(): EntityRepository
    {
        return $this->em->getRepository(AdditionalService::class);
    }

    public function getById(int $additionalServiceId): AdditionalService
    {
        $additionalService = $this->findById($additionalServiceId);

        if ($additionalService === null) {
            throw new AdditionalServiceNotFoundException(
                'Additional service with ID ' . $additionalServiceId . ' not found.',
            );
        }

        return $additionalService;
    }

    public function findById(int $additionalServiceId): ?AdditionalService
    {
        return $this->getAdditionalServiceRepository()->find($additionalServiceId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    public function getAllOrderedByPosition(): array
    {
        return $this->em->createQueryBuilder()
            ->select('ads, at')
            ->from(AdditionalService::class, 'ads')
            ->leftJoin('ads.translations', 'at')
            ->orderBy('ads.position', 'ASC')
            ->addOrderBy('ads.id', 'ASC')
            ->getQuery()->getResult();
    }

    protected function createEnabledByProductQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('pasd, ads, at')
            ->from(ProductAdditionalServiceDomain::class, 'pasd')
            ->join('pasd.additionalService', 'ads')
            ->leftJoin('ads.translations', 'at')
            ->join(
                'ads.domains',
                'asd',
                Join::WITH,
                'asd.domainId = :domainId',
            )
            ->where('pasd.domainId = :domainId')
            ->andWhere('asd.enabled = true')
            ->orderBy('ads.position', 'ASC')
            ->addOrderBy('ads.id', 'ASC')
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param int[] $productIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]>
     */
    public function getShownInFeedsIndexedByProductIds(array $productIds, int $domainId): array
    {
        $productAdditionalServiceDomains = $this->createEnabledByProductQueryBuilder($domainId)
            ->andWhere('asd.showInFeeds = true')
            ->andWhere('pasd.product IN (:productIds)')
            ->setParameter('productIds', $productIds)
            ->getQuery()->getResult();

        return $this->indexAdditionalServicesByProductId($productAdditionalServiceDomains);
    }

    public function useProductVatRateWhereVatIsMissing(int $domainId): void
    {
        $this->em->createQueryBuilder()
            ->update(AdditionalServiceDomain::class, 'asd')
            ->set('asd.useProductVatRate', 'true')
            ->where('asd.domainId = :domainId')->setParameter('domainId', $domainId)
            ->andWhere('asd.vat IS NULL')
            ->andWhere('asd.useProductVatRate = false')
            ->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductAdditionalServiceDomain[] $productAdditionalServiceDomains
     * @return array<int, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]>
     */
    protected function indexAdditionalServicesByProductId(array $productAdditionalServiceDomains): array
    {
        $additionalServicesIndexedByProductId = [];

        foreach ($productAdditionalServiceDomains as $productAdditionalServiceDomain) {
            $productId = $productAdditionalServiceDomain->getProduct()->getId();
            $additionalServicesIndexedByProductId[$productId][] = $productAdditionalServiceDomain->getAdditionalService();
        }

        return $additionalServicesIndexedByProductId;
    }
}
