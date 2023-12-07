<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\FrontendApi\Model\Image\ImageBatchLoadData;
use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Advert\Advert;

class AdvertImagesQuery extends ImagesQuery
{
    private const ENTITY_NAME = 'noticer';

    /**
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByAdvertPromiseQuery(Advert $advert, ?string $type): Promise
    {
        return $this->imagesBatchLoader->load(
            new ImageBatchLoadData(
                $advert->getId(),
                self::ENTITY_NAME,
                $type,
            ),
        );
    }

    /**
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByAdvertPromiseQuery(Advert $advert, ?string $type): Promise
    {
        return $this->mainImageByEntityIdPromiseQuery($advert->getId(), self::ENTITY_NAME, $type);
    }
}
