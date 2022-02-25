<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class BlogArticleImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    public const ENTITY_NAME = 'blogArticle';

    /**
     * @param array $data
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveByBlogArticle(array $data, ?string $type, ?array $sizes): Promise
    {
        return $this->resolveByEntityId($data['id'], self::ENTITY_NAME, $type, $sizes);
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByBlogArticle' => 'blogArticleImageResolver'];
    }
}
