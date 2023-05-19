<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\MassAction;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\MassAction\Exception\UnsupportedSelectionType;
use Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class ProductMassActionFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionRepository $productMassActionRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator $productHiddenRecalculator
     */
    public function __construct(
        protected readonly ProductMassActionRepository $productMassActionRepository,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly ProductHiddenRecalculator $productHiddenRecalculator,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionData $productMassActionData
     * @param \Doctrine\ORM\QueryBuilder $selectQueryBuilder
     * @param int[] $checkedProductIds
     */
    public function doMassAction(
        ProductMassActionData $productMassActionData,
        QueryBuilder $selectQueryBuilder,
        array $checkedProductIds,
    ) {
        $selectedProductIds = $this->getSelectedProductIds(
            $productMassActionData,
            $selectQueryBuilder,
            $checkedProductIds,
        );

        if ($productMassActionData->action !== ProductMassActionData::ACTION_SET) {
            return;
        }

        if ($productMassActionData->subject !== ProductMassActionData::SUBJECT_PRODUCT_HIDDEN) {
            return;
        }

        $this->productMassActionRepository->setHidden(
            $selectedProductIds,
            $productMassActionData->value === ProductMassActionData::VALUE_PRODUCT_HIDE,
        );
        $this->productHiddenRecalculator->calculateHiddenForAll();
        $this->productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionData $productMassActionData
     * @param \Doctrine\ORM\QueryBuilder $selectQueryBuilder
     * @param int[] $checkedProductIds
     * @return int[]
     */
    protected function getSelectedProductIds(
        ProductMassActionData $productMassActionData,
        QueryBuilder $selectQueryBuilder,
        array $checkedProductIds,
    ) {
        $selectedProductIds = [];

        if ($productMassActionData->selectType === ProductMassActionData::SELECT_TYPE_ALL_RESULTS) {
            $queryBuilder = clone $selectQueryBuilder;

            $results = $queryBuilder
                ->select('p.id')
                ->getQuery()
                ->getScalarResult();

            foreach ($results as $result) {
                $selectedProductIds[] = $result['id'];
            }
        } elseif ($productMassActionData->selectType === ProductMassActionData::SELECT_TYPE_CHECKED) {
            $selectedProductIds = $checkedProductIds;
        } else {
            throw new UnsupportedSelectionType($productMassActionData->selectType);
        }

        return $selectedProductIds;
    }
}
