<?php

declare(strict_types=1);

namespace App\Model\Product\Detail;

class ProductFileViewFactory
{
    /**
     * @param array $fileData
     * @return \App\Model\Product\Detail\ProductFileView
     */
    public function createFromArray(array $fileData): ProductFileView
    {
        return new ProductFileView(
            $fileData['anchor_text'],
            $fileData['url']
        );
    }

    /**
     * @param array $fileDataArray
     * @return \App\Model\Product\Detail\ProductFileView[]
     */
    public function createMultipleFromArray(array $fileDataArray): array
    {
        $productFileViews = [];
        foreach ($fileDataArray as $fileData) {
            $productFileViews[] = $this->createFromArray($fileData);
        }

        return $productFileViews;
    }
}
