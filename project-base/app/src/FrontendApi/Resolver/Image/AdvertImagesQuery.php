<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\FrontendApi\Model\Image\ImageBatchLoadData;
use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Model\Advert\Advert;

class AdvertImagesQuery extends ImagesQuery
{
    private const ENTITY_NAME = 'noticer';

    /**
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByAdvertPromiseQuery(Advert $advert, ?string $type, ?array $sizes): Promise
    {
        $sizeConfigs = $this->getSizesConfigsForAdvert($advert, $type, $sizes);

        return $this->imagesBatchLoader->load(
            new ImageBatchLoadData(
                $advert->getId(),
                self::ENTITY_NAME,
                $sizeConfigs,
                $type,
            ),
        );
    }

    /**
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByAdvertPromiseQuery(Advert $advert, ?string $type, ?string $size): Promise
    {
        return $this->mainImageByEntityIdPromiseQuery($advert->getId(), self::ENTITY_NAME, $type, $size);
    }

    /**
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @param array|null $sizes
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    private function getSizesConfigsForAdvert(Advert $advert, ?string $type, ?array $sizes): array
    {
        if ($sizes === null) {
            return $this->getSizesConfigs($type, [$advert->getPositionName(), ImageConfig::ORIGINAL_SIZE_NAME], self::ENTITY_NAME);
        }

        return $this->getSizesConfigs($type, $sizes, self::ENTITY_NAME);
    }
}
