<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Symfony\Component\Clock\DatePoint;

class ArticleResolverMap extends ResolverMap
{
    #[Override]
    protected function map(): array
    {
        $map['ArticleSite'] = [
            'slug' => function (array $articleData) {
                return '/' . $articleData['mainSlug'];
            },
            'createdAt' => static function (array $blogArticleData) {
                return new DatePoint($blogArticleData['createdAt']);
            },
        ];

        return $map;
    }
}
