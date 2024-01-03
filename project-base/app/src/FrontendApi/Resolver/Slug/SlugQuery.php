<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Slug;

use App\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use App\FrontendApi\Resolver\Products\Flag\FlagQuery;
use App\FrontendApi\Resolver\Slug\Exception\NoResultFoundForSlugUserError;
use App\FrontendApi\Resolver\Store\StoreQuery;
use App\Model\Article\Article;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Product;
use GraphQL\Type\Definition\ResolveInfo;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticleQuery;
use Shopsys\FrontendApiBundle\Model\Blog\Category\BlogCategoryQuery;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticleQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailQuery;

class SlugQuery extends AbstractQuery
{
    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticleQuery $articleQuery
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery $brandQuery
     * @param \Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticleQuery $blogArticleQuery
     * @param \Shopsys\FrontendApiBundle\Model\Blog\Category\BlogCategoryQuery $blogCategoryQuery
     * @param \App\FrontendApi\Resolver\Category\CategoryQuery $categoryQuery
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailQuery $productDetailQuery
     * @param \App\FrontendApi\Resolver\Store\StoreQuery $storeQuery
     * @param \App\FrontendApi\Resolver\Products\Flag\FlagQuery $flagQuery
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
        private readonly FlagQuery $flagQuery,
    ) {
    }

    /**
     * @param string $slug
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory|\App\Model\Category\Category|\App\Model\Product\Brand\Brand|\Shopsys\FrameworkBundle\Model\Store\Store|\App\Model\CategorySeo\ReadyCategorySeoMix|\App\Model\Product\Flag\Flag|array
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
                    $article = $this->articleQuery->articleByUuidOrUrlSlugQuery(null, $slugWithoutSlash);
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
                case ReadyCategorySeoMix::class:
                    return $this->categoryQuery->categoryOrSeoMixByUuidOrUrlSlugQuery($info, null, $slugWithoutSlash);
                case Flag::class:
                    return $this->flagQuery->flagByUuidOrUrlSlugQuery(null, $slugWithoutSlash);
                case Product::class:
                    $product = $this->productDetailQuery->productDetailQuery(null, $slugWithoutSlash);
                    $product[SlugResolverMap::SLUG_TYPE] = SlugResolverMap::SLUG_TYPE_PRODUCT;

                    return $product;
                case Store::class:
                    return $this->storeQuery->storeQuery(null, $slugWithoutSlash);
            }
        } catch (EntityNotFoundUserError) {
            throw new NoResultFoundForSlugUserError('No result found for request.');
        }

        throw new NoResultFoundForSlugUserError('No result found for request.');
    }
}
