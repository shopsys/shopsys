<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Image;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Utils\Utils;

class ImagesCountBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ImageApiFacade $imageApiFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[] $imagesBatchLoadData
     */
    public function loadByBatchData(array $imagesBatchLoadData): Promise
    {
        $imagesBatchLoadDataByEntityNameAndType = $this->getImageBatchLoadDataArrayByEntityAndType($imagesBatchLoadData);
        $imageCounts = [];

        foreach ($imagesBatchLoadDataByEntityNameAndType as $entityName => $dataByTypes) {
            foreach ($dataByTypes as $type => $imagesBatchLoadDataOfEntityAndType) {
                $imageCounts = array_merge(
                    $imageCounts,
                    $this->getImageCountsByEntityNameAndTypeIndexedByDataId(
                        $imagesBatchLoadDataOfEntityAndType,
                        $entityName,
                        $type,
                    ),
                );
            }
        }

        return $this->promiseAdapter->all($this->sortImageCountsByOriginalInputData($imageCounts, $imagesBatchLoadData));
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[] $imagesBatchLoadData
     * @return array<string, int>
     */
    protected function getImageCountsByEntityNameAndTypeIndexedByDataId(
        array $imagesBatchLoadData,
        string $entityName,
        string $type,
    ): array {
        if ($type === ImageEntityConfig::WITHOUT_NAME_KEY) {
            $type = null;
        }

        $entityIds = array_map(
            fn (ImageBatchLoadData $imageBatchLoadData) => $imageBatchLoadData->getEntityId(),
            $imagesBatchLoadData,
        );
        $imageCountsByEntityId = $this->imageApiFacade->getImageCountsIndexedByEntityId(
            $entityIds,
            $entityName,
            $type,
        );
        $imageCounts = [];

        foreach ($imagesBatchLoadData as $imageBatchLoadData) {
            $imageCounts[$imageBatchLoadData->getId()] = $imageCountsByEntityId[$imageBatchLoadData->getEntityId()];
        }

        return $imageCounts;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[] $imagesBatchLoadData
     * @return \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[][][]
     */
    protected function getImageBatchLoadDataArrayByEntityAndType(array $imagesBatchLoadData): array
    {
        $result = [];

        foreach ($imagesBatchLoadData as $imageBatchLoadData) {
            $entityName = $imageBatchLoadData->getEntityName();
            $type = Utils::ifNull($imageBatchLoadData->getType(), ImageEntityConfig::WITHOUT_NAME_KEY);
            $result[$entityName][$type][] = $imageBatchLoadData;
        }

        return $result;
    }

    /**
     * @param array<string, int> $imageCountsIndexedByImageBatchLoadDataId
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[] $imagesBatchLoadData
     * @return int[]
     */
    protected function sortImageCountsByOriginalInputData(
        array $imageCountsIndexedByImageBatchLoadDataId,
        array $imagesBatchLoadData,
    ): array {
        $sortedImageCounts = [];

        foreach ($imagesBatchLoadData as $imageBatchLoadData) {
            $sortedImageCounts[] = $imageCountsIndexedByImageBatchLoadDataId[$imageBatchLoadData->getId()] ?? 0;
        }

        return $sortedImageCounts;
    }
}
