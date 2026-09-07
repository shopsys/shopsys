<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;

class ArticleImagesQuery extends ImagesQuery
{
    protected const ENTITY_NAME = 'article';

    public function mainImageByArticlePromiseQuery(array $data, ?string $type): Promise
    {
        return $this->mainImageByEntityIdPromiseQuery($data['id'], static::ENTITY_NAME, $type);
    }
}
