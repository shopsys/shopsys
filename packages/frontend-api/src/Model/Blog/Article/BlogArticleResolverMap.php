<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrontendApiBundle\Model\Seo\SeoAttributesQueryDtoFactory;
use Symfony\Component\Clock\DatePoint;

class BlogArticleResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly SeoAttributesQueryDtoFactory $seoAttributesQueryDtoFactory,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly DataLoaderInterface $blogCategoriesVisibleBatchLoader,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'BlogArticle' => [
                'blogCategories' => function (array $blogArticleData) {
                    return $this->blogCategoriesVisibleBatchLoader
                        ->loadMany($blogArticleData['categories'])
                        ->then(fn (array $blogCategories): array => $this->getVisibleBlogCategoriesSortedByLft($blogCategories));
                },
                'publishDate' => static function (array $blogArticleData) {
                    return $blogArticleData['publishDate'] !== null ? new DatePoint($blogArticleData['publishDate']) : null;
                },
                'modifiedAt' => static function (array $articleData) {
                    return ($articleData['modifiedAt'] ?? null) !== null ? new DatePoint($articleData['modifiedAt']) : null;
                },
                'createdAt' => static function (array $blogArticleData) {
                    return new DatePoint($blogArticleData['createdAt']);
                },
                'slug' => static function (array $blogArticleData) {
                    return '/' . $blogArticleData['mainSlug'];
                },
                'link' => function (array $blogArticleData) {
                    return $this->friendlyUrlFacade->getAbsoluteUrlByMainFriendlyUrlSlug(
                        $this->domain->getId(),
                        $blogArticleData['mainSlug'],
                    );
                },
                'hreflangLinks' => function (array $blogArticleData) {
                    return $this->hreflangLinksFacade->getHreflangLinksWithIncludedDomainUrl($blogArticleData['hreflangLinks']);
                },
                'mainBlogCategoryUuid' => function (array $blogArticleData) {
                    return $blogArticleData['mainBlogCategoryUuid'];
                },
                'seo' => function (array $blogArticleData) {
                    return $this->seoAttributesQueryDtoFactory->create(
                        $blogArticleData['seoTitle'],
                        $blogArticleData['seoMetaDescription'],
                        $blogArticleData['seoH1'],
                        $blogArticleData['seoMetaRobots'],
                        $blogArticleData['seoCanonicalUrl'],
                    );
                },
            ],
        ];
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory|null> $blogCategories
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    protected function getVisibleBlogCategoriesSortedByLft(array $blogCategories): array
    {
        $visibleBlogCategories = array_values(array_filter($blogCategories));

        usort(
            $visibleBlogCategories,
            static fn (BlogCategory $first, BlogCategory $second): int => $first->getLft() <=> $second->getLft(),
        );

        return $visibleBlogCategories;
    }
}
