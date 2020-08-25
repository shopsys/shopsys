<?php

declare(strict_types=1);

namespace App\Form\Transformers;

use App\Model\Product\Series\Exception\ProductSeriesNotFoundException;
use App\Model\Product\Series\ProductSeriesRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductSeriesIdsToProductSeriesTransformer implements DataTransformerInterface
{
    /**
     * @var \App\Model\Product\Series\ProductSeriesRepository
     */
    protected $productSeriesRepository;

    /**
     * @param \App\Model\Product\Series\ProductSeriesRepository $productSeriesRepository
     */
    public function __construct(ProductSeriesRepository $productSeriesRepository)
    {
        $this->productSeriesRepository = $productSeriesRepository;
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries[]|null $productSeriesList
     * @return int[]
     */
    public function transform($productSeriesList): array
    {
        $productSeriesIds = [];
        if (is_iterable($productSeriesList)) {
            foreach ($productSeriesList as $productSeries) {
                $productSeriesIds[] = $productSeries->getId();
            }
        }

        return $productSeriesIds;
    }

    /**
     * @param int[] $productSeriesIds
     * @throws \Exception
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function reverseTransform($productSeriesIds): array
    {
        $productSeries = [];
        if (is_array($productSeries)) {
            foreach ($productSeriesIds as $productSeriesId) {
                try {
                    $productSeries[] = $this->productSeriesRepository->getById((int)$productSeriesId);
                } catch (TransformationFailedException $e) {
                    throw new ProductSeriesNotFoundException('Product series NOT FOUND, searched ID: ' . $productSeriesId);
                }
            }
        }

        return $productSeries;
    }
}
