<?php

declare(strict_types=1);

namespace App\Form\Admin\Transformer;

use App\Model\Stock\Exception\StockNotFoundException;
use App\Model\Stock\Stock;
use App\Model\Stock\StockRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

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
     * @param \App\Model\Stock\Stock|null $stock
     * @return int|null
     */
    public function transform($stock): ?int
    {
        return $stock !== null ? $stock->getId() : null;
    }

    /**
     * @param int|null $stockId
     * @return \App\Model\Stock\Stock|null
     */
    public function reverseTransform($stockId): ?Stock
    {
        if ($stockId === null) {
            return null;
        }

        try {
            return $this->stockRepository->getById((int)$stockId);
        } catch (StockNotFoundException $e) {
            throw new TransformationFailedException('Stock not found', 0, $e);
        }
    }
}
