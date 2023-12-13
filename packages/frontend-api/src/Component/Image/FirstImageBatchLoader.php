<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Image;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Utils\Utils;

class FirstImageBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageApiFacade $imageApiFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ImageApiFacade $imageApiFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[] $imagesBatchLoadData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByBatchData(array $imagesBatchLoadData): Promise
    {
        $imagesBatchLoadDataByEntityNameAndType = $this->getImageBatchLoadDataArrayIndexedByEntityAndType($imagesBatchLoadData);

        $imagesIndexedByImageBatchLoadDataId = [];

        foreach ($imagesBatchLoadDataByEntityNameAndType as $entityName => $dataByTypes) {
            foreach ($dataByTypes as $type => $imagesBatchLoadDataOfType) {
                $imagesIndexedByImageBatchLoadDataId = array_merge($imagesIndexedByImageBatchLoadDataId, $this->getImagesByEntityNameAndTypeIndexedByDataId($imagesBatchLoadDataOfType, $entityName, $type));
            }
        }

        return $this->promiseAdapter->all($this->sortImagesByOriginalInputData($imagesIndexedByImageBatchLoadDataId, $imagesBatchLoadData));
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[] $imagesBatchLoadData
     * @param string $entityName
     * @param string $type
     * @return array<string, array|null>
     */
    protected function getImagesByEntityNameAndTypeIndexedByDataId(
        array $imagesBatchLoadData,
        string $entityName,
        string $type,
    ): array {
        if ($type === ImageEntityConfig::WITHOUT_NAME_KEY) {
            $type = null;
        }
        $entityIds = array_map(fn (ImageBatchLoadData $imageBatchLoadData) => $imageBatchLoadData->getEntityId(), $imagesBatchLoadData);
        $imagesIndexedByEntityId = $this->imageApiFacade->getImagesByTypeAndPositionIndexedByEntityId($entityIds, $entityName, $type);

        $images = [];

        foreach ($imagesBatchLoadData as $imageBatchLoadData) {
            if (!isset($imagesIndexedByEntityId[$imageBatchLoadData->getEntityId()]) || $imagesIndexedByEntityId[$imageBatchLoadData->getEntityId()] === null) { // @phpstan-ignore-line
                continue;
            }

            $entityResolvedImage = $this->getResolvedImage($imagesIndexedByEntityId[$imageBatchLoadData->getEntityId()]);
            $images[$imageBatchLoadData->getId()] = $entityResolvedImage;
        }

        return $images;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[] $imagesBatchLoadData
     * @return \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[][][]
     */
    protected function getImageBatchLoadDataArrayIndexedByEntityAndType(array $imagesBatchLoadData): array
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
     * @param array<string, array|null> $imagesIndexedByImageBatchLoadDataId
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData[] $imagesBatchLoadData
     * @return array<int, array|null>
     */
    protected function sortImagesByOriginalInputData(
        array $imagesIndexedByImageBatchLoadDataId,
        array $imagesBatchLoadData,
    ): array {
        $sortedImages = [];

        foreach ($imagesBatchLoadData as $imageBatchLoadData) {
            if (array_key_exists($imageBatchLoadData->getId(), $imagesIndexedByImageBatchLoadDataId) === false) {
                $sortedImages[] = null;

                continue;
            }

            $sortedImages[] = $imagesIndexedByImageBatchLoadDataId[$imageBatchLoadData->getId()];
        }

        return array_values($sortedImages);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @return array
     */
    protected function getResolvedImage(Image $image): array
    {
        return [
            'url' => $this->imageFacade->getImageUrl(
                $this->domain->getCurrentDomainConfig(),
                $image,
                $image->getType(),
            ),
            'name' => $image->getName(),
        ];
    }
}
