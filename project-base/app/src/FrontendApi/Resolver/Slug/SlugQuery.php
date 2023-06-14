<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Slug;

use App\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use App\FrontendApi\Resolver\Article\ArticleQuery;
use App\FrontendApi\Resolver\Blog\Article\BlogArticleQuery;
use App\FrontendApi\Resolver\Blog\Category\BlogCategoryQuery;
use App\FrontendApi\Resolver\Category\CategorySeo\ReadyCategorySeoMixQuery;
use App\FrontendApi\Resolver\Products\Flag\FlagQuery;
use App\FrontendApi\Resolver\Products\ProductsQuery;
use App\FrontendApi\Resolver\Slug\Exception\NoResultFoundForSlugUserError;
use App\FrontendApi\Resolver\Store\StoreQuery;
use App\Model\Article\Article;
use App\Model\Blog\Article\BlogArticle;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Product;
use App\Model\Store\Store;
use GraphQL\Type\Definition\ResolveInfo;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailQuery;

class SlugQuery extends AbstractQuery
{
    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Resolver\Article\ArticleQuery $articleQuery
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery $brandQuery
     * @param \App\FrontendApi\Resolver\Blog\Article\BlogArticleQuery $blogArticleQuery
     * @param \App\FrontendApi\Resolver\Blog\Category\BlogCategoryQuery $blogCategoryQuery
     * @param \App\FrontendApi\Resolver\Category\CategoryQuery $categoryQuery
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailQuery $productDetailQuery
     * @param \App\FrontendApi\Resolver\Store\StoreQuery $storeQuery
     * @param \App\FrontendApi\Resolver\Category\CategorySeo\ReadyCategorySeoMixQuery $readyCategorySeoMixQuery
     * @param \App\FrontendApi\Resolver\Products\Flag\FlagQuery $flagQuery
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        private readonly FriendlyUrlRepository $friendlyUrlRepository,
        private readonly Domain $domain,
        private readonly ArticleQuery $articleQuery,
        private readonly BrandQuery $brandQuery,
        private readonly BlogArticleQuery $blogArticleQuery,
        private readonly BlogCategoryQuery $blogCategoryQuery,
        private readonly CategoryQuery $categoryQuery,
        private readonly ProductDetailQuery $productDetailQuery,
        private readonly StoreQuery $storeQuery,
        private readonly ReadyCategorySeoMixQuery $readyCategorySeoMixQuery,
        private readonly FlagQuery $flagQuery,
        private readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
    ) {
    }

    /**
     * @param string $slug
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @return \App\Model\Blog\Category\BlogCategory|\App\Model\Category\Category|\App\Model\Product\Brand\Brand|\App\Model\Store\Store|\App\Model\CategorySeo\ReadyCategorySeoMix|\App\Model\Product\Flag\Flag|array
     */
    public function slugQuery(string $slug, ResolveInfo $info)
    {
        $slugWithoutSlash = ltrim($slug, '/');
        $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($this->domain->getId(), $slugWithoutSlash);

        if ($friendlyUrl === null) {
            throw new NoResultFoundForSlugUserError('No result found for request.');
        }

        $routeNameToEntityMap = $this->friendlyUrlRepository->getRouteNameToEntityMap();
        $entity = $routeNameToEntityMap[$friendlyUrl->getRouteName()];

        try {
            switch ($entity) {
                case Article::class:
                    $article = $this->articleQuery->articleQuery(null, $slugWithoutSlash);
                    $article[SlugResolverMap::SLUG_TYPE] = SlugResolverMap::SLUG_TYPE_ARTICLE;

                    return $article;
                case Brand::class:
                    /** @var \App\Model\Product\Brand\Brand $brand */
                    $brand = $this->brandQuery->brandByUuidOrUrlSlugQuery(null, $slugWithoutSlash);

                    return $brand;
                case BlogArticle::class:
                    $blogArticle = $this->blogArticleQuery->blogArticleByUuidOrUrlSlugQuery(null, $slugWithoutSlash);
                    $blogArticle[SlugResolverMap::SLUG_TYPE] = SlugResolverMap::SLUG_TYPE_BLOG_ARTICLE;

                    return $blogArticle;
                case BlogCategory::class:
                    return $this->blogCategoryQuery->blogCategoryByUuidOrUrlSlugQuery(null, $slugWithoutSlash);
                case Category::class:
                    /** @var \App\Model\Category\Category $category */
                    $category = $this->categoryQuery->categoryByUuidOrUrlSlugQuery(null, $slugWithoutSlash);
                    $matchingReadyCategorySeoMix = $this->findMatchingReadyCategorySeoMix($info, $category);

                    return $matchingReadyCategorySeoMix ?? $category;
                case Flag::class:
                    return $this->flagQuery->flagByUuidOrUrlSlugQuery(null, $slugWithoutSlash);
                case Product::class:
                    $product = $this->productDetailQuery->productDetailQuery(null, $slugWithoutSlash);
                    $product[SlugResolverMap::SLUG_TYPE] = SlugResolverMap::SLUG_TYPE_PRODUCT;

                    return $product;
                case Store::class:
                    return $this->storeQuery->storeQuery(null, $slugWithoutSlash);
                case ReadyCategorySeoMix::class:
                    $readyCategorySeoMix = $this->readyCategorySeoMixQuery->readyCategorySeoMixQuery($slugWithoutSlash);

                    if ($this->isSortingDifferentFromReadyCategorySeoMix($info, $readyCategorySeoMix) || $this->isFilterSet($info)) {
                        return $readyCategorySeoMix->getCategory();
                    }

                    return $readyCategorySeoMix;
            }
        } catch (EntityNotFoundUserError) {
            throw new NoResultFoundForSlugUserError('No result found for request.');
        }

        throw new NoResultFoundForSlugUserError('No result found for request.');
    }

    /**
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @param \App\Model\Category\Category $category
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    private function findMatchingReadyCategorySeoMix(ResolveInfo $info, Category $category): ?ReadyCategorySeoMix
    {
        $variableValues = $info->variableValues;
        $onlyInStock = $variableValues['filter']['onlyInStock'] ?? false;
        $minimalPrice = $variableValues['filter']['minimalPrice'] ?? null;
        $maximalPrice = $variableValues['filter']['maximalPrice'] ?? null;
        $brandChoices = $variableValues['filter']['brands'] ?? [];

        if ($onlyInStock || isset($minimalPrice) || isset($maximalPrice) || count($brandChoices) > 0) {
            return null;
        }

        return $this->readyCategorySeoMixFacade->findReadyCategorySeoMixByQueryInputData(
            $category->getId(),
            $variableValues['filter']['parameters'] ?? [],
            $variableValues['filter']['flags'] ?? [],
            $variableValues['orderingMode'] ?? ProductsQuery::getDefaultOrderingModeForListing(),
        );
    }

    /**
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo
     * @return bool
     */
    private function isFilterSet(ResolveInfo $resolveInfo): bool
    {
        $variableValues = $resolveInfo->variableValues;
        $onlyInStock = $variableValues['filter']['onlyInStock'] ?? false;
        $minimalPrice = $variableValues['filter']['minimalPrice'] ?? null;
        $maximalPrice = $variableValues['filter']['maximalPrice'] ?? null;
        $parameters = $variableValues['filter']['parameters'] ?? [];
        $flags = $variableValues['filter']['flags'] ?? [];
        $brands = $variableValues['filter']['brands'] ?? [];

        return $onlyInStock || $minimalPrice !== null || $maximalPrice !== null || count($parameters) > 0 || count($flags) > 0 || count($brands) > 0;
    }

    /**
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @return bool
     */
    private function isSortingDifferentFromReadyCategorySeoMix(
        ResolveInfo $resolveInfo,
        ReadyCategorySeoMix $readyCategorySeoMix,
    ): bool {
        $variableValues = $resolveInfo->variableValues;
        $sorting = $variableValues['orderingMode'] ?? null;

        if ($sorting === null) {
            return false;
        }

        return strtolower($sorting) !== strtolower($readyCategorySeoMix->getOrdering());
    }
}
