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
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByBlogArticlePromiseQuery(array $data, ?string $type, ?array $sizes): Promise
    {
        return $this->resolveByEntityIdPromise($data['id'], self::ENTITY_NAME, $type, $sizes);
    }

    /**
     * @param array $data
     * @param string|null $type
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByBlogArticlePromiseQuery(array $data, ?string $type, ?string $size): Promise
    {
        return $this->mainImageByEntityIdPromiseQuery($data['id'], self::ENTITY_NAME, $type, $size);
    }
}
