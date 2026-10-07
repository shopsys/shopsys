<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrontendApiBundle\Model\Seo\OrganizationQueryDto;

class OrganizationImagesQuery extends ImagesQuery
{
    protected const string ENTITY_NAME = 'organization';

    public function logoByOrganizationPromiseQuery(OrganizationQueryDto $organizationQueryDto): ?Promise
    {
        if ($organizationQueryDto->id === null) {
            return null;
        }

        return $this->mainImageByEntityIdPromiseQuery($organizationQueryDto->id, static::ENTITY_NAME, null);
    }
}
