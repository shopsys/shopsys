<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductListAdminRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @param int $pricingGroupId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getProductListQueryBuilder($pricingGroupId)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('p, pt, pmip.inputPrice AS priceForProductList')
            ->from(Product::class, 'p')
            ->leftJoin(
                ProductManualInputPrice::class,
                'pmip',
                Join::WITH,
                'pmip.product = p.id AND pmip.pricingGroup = :pricingGroupId',
            )
            ->leftJoin('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameters([
                'locale' => $this->localization->getAdminLocale(),
                'pricingGroupId' => $pricingGroupId,
            ]);

        return $queryBuilder;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     */
    public function extendQueryBuilderByQuickSearchData(
        QueryBuilder $queryBuilder,
        QuickSearchFormData $quickSearchData,
    ) {
        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder->andWhere('
                (
                    NORMALIZED(pt.name) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(p.catnum) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(p.partno) LIKE NORMALIZED(:text)
                )');
            $querySearchText = DatabaseSearching::getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }
    }
}
