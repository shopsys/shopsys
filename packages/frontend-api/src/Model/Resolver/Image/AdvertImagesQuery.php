<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;

class AdvertImagesQuery extends ImagesQuery
{
    protected const ENTITY_NAME = 'noticer';

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
                static::ENTITY_NAME,
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
        return $this->mainImageByEntityIdPromiseQuery($advert->getId(), static::ENTITY_NAME, $type);
    }
}
