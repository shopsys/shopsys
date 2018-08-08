<?php

namespace Shopsys\FrameworkBundle\Model\Product\MassAction;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class ProductMassActionFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionRepository
     */
    protected $productMassActionRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    protected $productVisibilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator
     */
    protected $productHiddenRecalculator;

    public function __construct(
        ProductMassActionRepository $productMassActionRepository,
        ProductVisibilityFacade $productVisibilityFacade,
        ProductHiddenRecalculator $productHiddenRecalculator
    ) {
        $this->productMassActionRepository = $productMassActionRepository;
        $this->productVisibilityFacade = $productVisibilityFacade;
        $this->productHiddenRecalculator = $productHiddenRecalculator;
    }

    /**
     * @param int[] $checkedProductIds
     */
    public function doMassAction(
        ProductMassActionData $productMassActionData,
        QueryBuilder $selectQueryBuilder,
        array $checkedProductIds
    ) {
        $selectedProductIds = $this->getSelectedProductIds(
            $productMassActionData,
            $selectQueryBuilder,
            $checkedProductIds
        );

        if ($productMassActionData->action === ProductMassActionData::ACTION_SET) {
            if ($productMassActionData->subject === ProductMassActionData::SUBJECT_PRODUCT_HIDDEN) {
                $this->productMassActionRepository->setHidden(
                    $selectedProductIds,
                    $productMassActionData->value === ProductMassActionData::VALUE_PRODUCT_HIDE
                );
                $this->productHiddenRecalculator->calculateHiddenForAll();
                $this->productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();
            }
        }
    }

    /**
     * @param int[] $checkedProductIds
     * @return int[]
     */
    protected function getSelectedProductIds(
        ProductMassActionData $productMassActionData,
        QueryBuilder $selectQueryBuilder,
        array $checkedProductIds
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
            throw new \Shopsys\FrameworkBundle\Model\Product\MassAction\Exception\UnsupportedSelectionType($productMassActionData->selectType);
        }

        return $selectedProductIds;
    }
}
