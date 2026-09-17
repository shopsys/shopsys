<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrontendApiBundle\Model\Seo\SeoAttributesQueryDtoFactory;
use Symfony\Component\Clock\DatePoint;

class ArticleResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly SeoAttributesQueryDtoFactory $seoAttributesQueryDtoFactory,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        $map['ArticleSite'] = [
            'slug' => function (array $articleData) {
                return '/' . $articleData['mainSlug'];
            },
            'seo' => function (array $articleData) {
                return $this->seoAttributesQueryDtoFactory->create(
                    $articleData['seoTitle'],
                    $articleData['seoMetaDescription'],
                    $articleData['seoH1'],
                    $articleData['seoMetaRobots'],
                    $articleData['seoCanonicalUrl'],
                );
            },
            'createdAt' => static function (array $blogArticleData) {
                return new DatePoint($blogArticleData['createdAt']);
            },
        ];

        return $map;
    }
}
