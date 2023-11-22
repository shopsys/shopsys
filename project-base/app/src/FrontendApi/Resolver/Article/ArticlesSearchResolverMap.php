<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Article;

use InvalidArgumentException;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class ArticlesSearchResolverMap extends ResolverMap
{
    /**
     * @return string|array<'ArticleInterface', array<'%%resolveType', \Closure(mixed $data): string>>
     */
    protected function map(): string|array
    {
        return [
            'ArticleInterface' => [
                self::RESOLVE_TYPE => function ($data) {
                    if (isset($data['index'])) {
                        if ($data['index'] === 'article') {
                            return 'ArticleSite';
                        }

                        if ($data['index'] === 'blog_article') {
                            return 'BlogArticle';
                        }
                    }

                    throw new InvalidArgumentException('Article data must contain "index" key that must be one the following values: "article", "blog_article".');
                },
            ],
        ];
    }
}
