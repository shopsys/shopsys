<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;

class BlogArticleImagesQuery extends ImagesQuery
{
    protected const ENTITY_NAME = 'blogArticle';

    /**
     * @param array $data
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByBlogArticlePromiseQuery(array $data, ?string $type): Promise
    {
        return $this->resolveByEntityIdPromise($data['id'], static::ENTITY_NAME, $type);
    }

    /**
     * @param array $data
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByBlogArticlePromiseQuery(array $data, ?string $type): Promise
    {
        return $this->mainImageByEntityIdPromiseQuery($data['id'], static::ENTITY_NAME, $type);
    }
}
