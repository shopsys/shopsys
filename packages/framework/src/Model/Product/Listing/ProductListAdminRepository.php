<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;

class ProductListAdminRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Localization $localization,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    public function getProductListQueryBuilder(int $pricingGroupId): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder();

        $subQueryBuilder = $this->em->createQueryBuilder();
        $subQueryBuilder
            ->select('COUNT(pd.id)')
            ->from(ProductDomain::class, 'pd')
            ->where('pd.product = p.id')
            ->andWhere('pd.calculatedSellingDenied = true');

        $queryBuilder
            ->select('
                p.id,
                pt.name,
                p.variantType,
                p.productType,
                p.catnum,
                p.ean,
                pmip.inputPrice AS priceForProductList,
                (' . $subQueryBuilder->getDQL() . ') AS sellingDeniedDomainsCount
            ')
            ->from(Product::class, 'p')
            ->leftJoin(
                ProductManualInputPrice::class,
                'pmip',
                Join::WITH,
                'pmip.product = p.id AND pmip.pricingGroup = :pricingGroupId AND pmip.currency = :currency',
            )
            ->leftJoin('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities())
            ->setParameter('pricingGroupId', $pricingGroupId)
            ->setParameter('currency', $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID));

        return $queryBuilder;
    }

    public function extendQueryBuilderByQuickSearchData(
        QueryBuilder $queryBuilder,
        QuickSearchFormData $quickSearchData,
    ): void {
        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $uuidCondition = '';

            if (Uuid::isValid($quickSearchData->text)) {
                $uuidCondition = 'p.uuid = :exactText OR ';
                $queryBuilder->setParameter('exactText', $quickSearchData->text);
            }

            $queryBuilder->andWhere('
                (
                    ' . $uuidCondition . '
                    NORMALIZED(pt.name) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(p.catnum) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(p.partno) LIKE NORMALIZED(:text)
                )');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }
    }
}
