<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class BlogArticleImagesQuery extends ImagesQuery implements AliasedInterface
{
    public const ENTITY_NAME = 'blogArticle';

    /**
     * @param array $data
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByBlogArticlePromiseQuery(array $data, ?string $type): Promise
    {
        return $this->resolveByEntityIdPromise($data['id'], self::ENTITY_NAME, $type);
    }

    /**
     * @param array $data
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByBlogArticlePromiseQuery(array $data, ?string $type): Promise
    {
        return $this->mainImageByEntityIdPromiseQuery($data['id'], self::ENTITY_NAME, $type);
    }
}
