<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductFilterRepository
{
    const DAYS_FOR_STOCK_FILTER = 0;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService
     */
    private $queryBuilderService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterRepository
     */
    private $parameterFilterRepository;

    public function __construct(
        QueryBuilderService $queryBuilderService,
        ParameterFilterRepository $parameterFilterRepository
    ) {
        $this->queryBuilderService = $queryBuilderService;
        $this->parameterFilterRepository = $parameterFilterRepository;
    }

    public function applyFiltering(
        QueryBuilder $productsQueryBuilder,
        ProductFilterData $productFilterData,
        PricingGroup $pricingGroup
    ): void {
        $this->filterByPrice(
            $productsQueryBuilder,
            $productFilterData->minimalPrice,
            $productFilterData->maximalPrice,
            $pricingGroup
        );
        $this->filterByStock(
            $productsQueryBuilder,
            $productFilterData->inStock
        );
        $this->filterByFlags(
            $productsQueryBuilder,
            $productFilterData->flags
        );
        $this->filterByBrands(
            $productsQueryBuilder,
            $productFilterData->brands
        );
        $this->parameterFilterRepository->filterByParameters(
            $productsQueryBuilder,
            $productFilterData->parameters
        );
    }
    
    private function filterByPrice(
        QueryBuilder $productsQueryBuilder,
        string $minimalPrice,
        string $maximalPrice,
        PricingGroup $pricingGroup
    ): void {
        if ($maximalPrice !== null || $minimalPrice !== null) {
            $priceLimits = 'pcp.product = p AND pcp.pricingGroup = :pricingGroup';
            if ($minimalPrice !== null) {
                $priceLimits .= ' AND pcp.priceWithVat >= :minimalPrice';
                $productsQueryBuilder->setParameter('minimalPrice', $minimalPrice);
            }
            if ($maximalPrice !== null) {
                $priceLimits .= ' AND pcp.priceWithVat <= :maximalPrice';
                $productsQueryBuilder->setParameter('maximalPrice', $maximalPrice);
            }
            $this->queryBuilderService->addOrExtendJoin(
                $productsQueryBuilder,
                ProductCalculatedPrice::class,
                'pcp',
                $priceLimits
            );
            $productsQueryBuilder->setParameter('pricingGroup', $pricingGroup);
        }
    }
    
    public function filterByStock(QueryBuilder $productsQueryBuilder, bool $filterByStock): void
    {
        if ($filterByStock) {
            $this->queryBuilderService->addOrExtendJoin(
                $productsQueryBuilder,
                Availability::class,
                'a',
                'p.calculatedAvailability = a'
            );
            $productsQueryBuilder->andWhere('a.dispatchTime = :dispatchTime');
            $productsQueryBuilder->setParameter('dispatchTime', self::DAYS_FOR_STOCK_FILTER);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $flags
     */
    private function filterByFlags(QueryBuilder $productsQueryBuilder, array $flags): void
    {
        $flagsCount = count($flags);
        if ($flagsCount !== 0) {
            $flagsQueryBuilder = $this->getFlagsQueryBuilder($flags, $productsQueryBuilder->getEntityManager());

            $productsQueryBuilder->andWhere($productsQueryBuilder->expr()->exists($flagsQueryBuilder));
            foreach ($flagsQueryBuilder->getParameters() as $parameter) {
                $productsQueryBuilder->setParameter($parameter->getName(), $parameter->getValue());
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[] $brands
     */
    private function filterByBrands(QueryBuilder $productsQueryBuilder, array $brands): void
    {
        $brandsCount = count($brands);
        if ($brandsCount !== 0) {
            $brandsQueryBuilder = $this->getBrandsQueryBuilder($brands, $productsQueryBuilder->getEntityManager());

            $productsQueryBuilder->andWhere($productsQueryBuilder->expr()->exists($brandsQueryBuilder));
            foreach ($brandsQueryBuilder->getParameters() as $parameter) {
                $productsQueryBuilder->setParameter($parameter->getName(), $parameter->getValue());
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $flags
     */
    private function getFlagsQueryBuilder(array $flags, EntityManagerInterface $em): \Doctrine\ORM\QueryBuilder
    {
        $flagsQueryBuilder = $em->createQueryBuilder();

        $flagsQueryBuilder
            ->select('1')
            ->from(Product::class, 'pf')
            ->join('pf.flags', 'f')
            ->where('pf = p')
            ->andWhere('f IN (:flags)')->setParameter('flags', $flags);

        return $flagsQueryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[] $brands
     */
    private function getBrandsQueryBuilder(array $brands, EntityManagerInterface $em): \Doctrine\ORM\QueryBuilder
    {
        $brandsQueryBuilder = $em->createQueryBuilder();

        $brandsQueryBuilder
            ->select('1')
            ->from(Product::class, 'pb')
            ->join('pb.brand', 'b')
            ->where('pb = p')
            ->andWhere('b IN (:brands)')->setParameter('brands', $brands);

        return $brandsQueryBuilder;
    }
}
