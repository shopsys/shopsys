<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use DateTime;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;

class BlogArticleResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'BlogArticle' => [
                'blogCategories' => function (array $blogArticleData) {
                    return $this->blogCategoryFacade->getVisibleByIds($this->domain->getId(), $blogArticleData['categories']);
                },
                'publishDate' => static function (array $blogArticleData) {
                    return new DateTime($blogArticleData['publishDate']);
                },
                'createdAt' => static function (array $blogArticleData) {
                    return new DateTime($blogArticleData['createdAt']);
                },
                'slug' => static function (array $blogArticleData) {
                    return '/' . $blogArticleData['mainSlug'];
                },
                'link' => static function (array $blogArticleData) {
                    return $blogArticleData['url'];
                },
                'hreflangLinks' => function (array $blogArticleData) {
                    return $blogArticleData['hreflangLinks'];
                },
                'mainBlogCategoryUuid' => function (array $blogArticleData) {
                    return $blogArticleData['mainBlogCategoryUuid'];
                },
            ],
        ];
    }
}
