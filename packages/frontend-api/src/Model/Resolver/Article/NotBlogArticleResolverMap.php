<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Resolver\ResolverMap;

class NotBlogArticleResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'NotBlogArticleInterface' => [
                self::RESOLVE_TYPE => function ($data) {
                    if ($data['type'] === 'site') {
                        return 'ArticleSite';
                    }

                    if ($data['type'] === 'link') {
                        return 'ArticleLink';
                    }
                },
            ],
        ];
    }
}
