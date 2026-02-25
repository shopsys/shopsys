<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Symfony\Component\Clock\DatePoint;

class BlogArticleResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'BlogArticle' => [
                'blogCategories' => function (array $blogArticleData) {
                    return $this->blogCategoryFacade->getVisibleByIds($this->domain->getId(), $blogArticleData['categories']);
                },
                'publishDate' => static function (array $blogArticleData) {
                    return $blogArticleData['publishDate'] !== null ? new DatePoint($blogArticleData['publishDate']) : null;
                },
                'createdAt' => static function (array $blogArticleData) {
                    return new DatePoint($blogArticleData['createdAt']);
                },
                'slug' => static function (array $blogArticleData) {
                    return '/' . $blogArticleData['mainSlug'];
                },
                'link' => function (array $blogArticleData) {
                    return $this->domain->getUrl() . '/' . $blogArticleData['mainSlug'];
                },
                'hreflangLinks' => function (array $blogArticleData) {
                    return $this->hreflangLinksFacade->getHreflangLinksWithIncludedDomainUrl($blogArticleData['hreflangLinks']);
                },
                'mainBlogCategoryUuid' => function (array $blogArticleData) {
                    return $blogArticleData['mainBlogCategoryUuid'];
                },
            ],
        ];
    }
}
