<?php

declare(strict_types=1);

namespace App\Form\Admin\Transformer;

use App\Model\Stock\Exception\StockNotFoundException;
use App\Model\Stock\StockRepository;
use Symfony\Component\Form\DataTransformerInterface;

class StockIdToStockTransformer implements DataTransformerInterface
{
    /**
     * @var \App\Model\Stock\StockRepository
     */
    private $stockRepository;

    /**
     * @param \App\Model\Stock\StockRepository $stockRepository
     */
    public function __construct(StockRepository $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }

    /**
     * @param \App\Model\Stock\Stock[]|null $stocksByProductTypeId
     * @return int[]
     */
    public function transform($stocksByProductTypeId)
    {
        $stockIds = [];

        if (is_iterable($stocksByProductTypeId)) {
            foreach ($stocksByProductTypeId as $productTypeId => $stock) {
                $stockIds[$productTypeId] = $stock->getId();
            }
        }

        return $stockIds;
    }

    /**
     * @param int[]|null[] $stockIdsByProductTypeId
     * @return \App\Model\Stock\Stock[]|null
     */
    public function reverseTransform($stockIdsByProductTypeId)
    {
        $stocks = [];
        if (is_array($stockIdsByProductTypeId)) {
            foreach ($stockIdsByProductTypeId as $productTypeId => $stockId) {
                if ($stockId === null) {
                    continue;
                }

                try {
                    $stocks[$productTypeId] = $this->stockRepository->getById($stockId);
                } catch (StockNotFoundException $e) {
                    throw new \Symfony\Component\Form\Exception\TransformationFailedException('Stock not found', 0, $e);
                }
            }
        }

        return $stocks;
    }
}
