<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative;

class SalesRepresentativeImagesQuery extends ImagesQuery
{
    protected const SALES_REPRESENTATIVE_ENTITY_NAME = 'salesRepresentative';

    public function imageBySalesRepresentativePromiseQuery(SalesRepresentative $data, ?string $type): Promise
    {
        return $this->mainImageByEntityIdPromiseQuery($data->getId(), static::SALES_REPRESENTATIVE_ENTITY_NAME, $type);
    }
}
