<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use DateTime;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;

class ArticleResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    #[Override]
    protected function map(): array
    {
        $map['ArticleSite'] = [
            'slug' => function (array $articleData) {
                return '/' . $articleData['mainSlug'];
            },
            'createdAt' => static function (array $blogArticleData) {
                return new DateTime($blogArticleData['createdAt']);
            },
        ];

        return $map;
    }
}
