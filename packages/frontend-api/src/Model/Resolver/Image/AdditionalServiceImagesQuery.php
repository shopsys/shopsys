<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrontendApiBundle\Model\AdditionalService\AdditionalServiceQueryDto;

class AdditionalServiceImagesQuery extends ImagesQuery
{
    protected const string ENTITY_NAME = 'additionalService';

    public function mainImageByAdditionalServicePromiseQuery(
        AdditionalServiceQueryDto $additionalServiceQueryDto,
        ?string $type,
    ): Promise {
        return $this->mainImageByEntityIdPromiseQuery($additionalServiceQueryDto->id, static::ENTITY_NAME, $type);
    }
}
