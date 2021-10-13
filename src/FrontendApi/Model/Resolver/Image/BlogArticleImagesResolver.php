<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class BlogArticleImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    /**
     * @param array $data
     * @param string|null $type
     * @param array|null $sizes
     * @return array|null
     */
    public function resolveByBlogArticle(array $data, ?string $type, ?array $sizes): ?array
    {
        $images = $this->resolveByEntityId($data['id'], 'blogArticle', $type, $sizes);
        return array_shift($images);
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByBlogArticle' => 'blogArticleImageResolver'];
    }
}
