<?php

declare(strict_types=1);

namespace App\Model\Product\Listing;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminRepository as BaseProductListAdminRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductListAdminRepository extends BaseProductListAdminRepository
{
    /**
     * @param int $pricingGroupId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getProductListQueryBuilder($pricingGroupId): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('p, pt, pd.lowPriceWithVat AS priceForProductList')
            ->from(Product::class, 'p')
            ->leftJoin(
                'p.domains',
                'pd',
                Join::WITH,
                'pd.product = p AND pd.domainId = :domainId'
            )
            ->leftJoin('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameters([
                'domainId' => Domain::FIRST_DOMAIN_ID,
                'locale' => $this->localization->getAdminLocale(),
            ]);

        return $queryBuilder;
    }
}
