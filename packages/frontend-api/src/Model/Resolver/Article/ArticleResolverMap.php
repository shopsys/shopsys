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
            'modifiedAt' => static function (array $articleData) {
                return ($articleData['modifiedAt'] ?? null) !== null ? new DatePoint($articleData['modifiedAt']) : null;
            },
            'publishDate' => static function (array $articleData) {
                return ($articleData['publishDate'] ?? null) !== null ? new DatePoint($articleData['publishDate']) : null;
            },
            'createdAt' => static function (array $blogArticleData) {
                return new DatePoint($blogArticleData['createdAt']);
            },
        ];

        return $map;
    }
}
