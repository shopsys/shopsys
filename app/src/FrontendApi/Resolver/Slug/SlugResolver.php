<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Slug;

use App\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use App\FrontendApi\Resolver\Article\ArticleResolver;
use App\FrontendApi\Resolver\Blog\Article\BlogArticleResolver;
use App\FrontendApi\Resolver\Blog\Category\BlogCategoryResolver;
use App\FrontendApi\Resolver\Category\CategorySeo\ReadyCategorySeoMixResolver;
use App\FrontendApi\Resolver\Products\Flag\FlagResolver;
use App\FrontendApi\Resolver\Slug\Exception\NoResultFoundForSlugUserError;
use App\FrontendApi\Resolver\Store\StoreResolver;
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
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandResolver;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolver;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailResolver;

class SlugResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private ReadyCategorySeoMixFacade $readyCategorySeoMixFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    private FriendlyUrlRepository $friendlyUrlRepository;

    /**
     * @var \App\FrontendApi\Resolver\Article\ArticleResolver
     */
    private ArticleResolver $articleResolver;

    /**
     * @var \App\FrontendApi\Resolver\Blog\Article\BlogArticleResolver
     */
    private BlogArticleResolver $blogArticleResolver;

    /**
     * @var \App\FrontendApi\Resolver\Blog\Category\BlogCategoryResolver
     */
    private BlogCategoryResolver $blogCategoryResolver;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandResolver
     */
    private BrandResolver $brandResolver;

    /**
     * @var \App\FrontendApi\Resolver\Category\CategoryResolver
     */
    private CategoryResolver $categoryResolver;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailResolver
     */
    private ProductDetailResolver $productDetailResolver;

    /**
     * @var \App\FrontendApi\Resolver\Store\StoreResolver
     */
    private StoreResolver $storeResolver;

    /**
     * @var \App\FrontendApi\Resolver\Category\CategorySeo\ReadyCategorySeoMixResolver
     */
    private ReadyCategorySeoMixResolver $readyCategorySeoMixResolver;

    /**
     * @var \App\FrontendApi\Resolver\Products\Flag\FlagResolver
     */
    private FlagResolver $flagResolver;

    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Resolver\Article\ArticleResolver $articleResolver
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandResolver $brandResolver
     * @param \App\FrontendApi\Resolver\Blog\Article\BlogArticleResolver $blogArticleResolver
     * @param \App\FrontendApi\Resolver\Blog\Category\BlogCategoryResolver $blogCategoryResolver
     * @param \App\FrontendApi\Resolver\Category\CategoryResolver $categoryResolver
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailResolver $productDetailResolver
     * @param \App\FrontendApi\Resolver\Store\StoreResolver $storeResolver
     * @param \App\FrontendApi\Resolver\Category\CategorySeo\ReadyCategorySeoMixResolver $readyCategorySeoMixResolver
     * @param \App\FrontendApi\Resolver\Products\Flag\FlagResolver $flagResolver
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        FriendlyUrlRepository $friendlyUrlRepository,
        Domain $domain,
        ArticleResolver $articleResolver,
        BrandResolver $brandResolver,
        BlogArticleResolver $blogArticleResolver,
        BlogCategoryResolver $blogCategoryResolver,
        CategoryResolver $categoryResolver,
        ProductDetailResolver $productDetailResolver,
        StoreResolver $storeResolver,
        ReadyCategorySeoMixResolver $readyCategorySeoMixResolver,
        FlagResolver $flagResolver,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
    ) {
        $this->friendlyUrlRepository = $friendlyUrlRepository;
        $this->domain = $domain;
        $this->articleResolver = $articleResolver;
        $this->blogArticleResolver = $blogArticleResolver;
        $this->blogCategoryResolver = $blogCategoryResolver;
        $this->brandResolver = $brandResolver;
        $this->categoryResolver = $categoryResolver;
        $this->productDetailResolver = $productDetailResolver;
        $this->storeResolver = $storeResolver;
        $this->readyCategorySeoMixResolver = $readyCategorySeoMixResolver;
        $this->flagResolver = $flagResolver;
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
    }

    /**
     * @param string $slug
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @return \App\Model\Blog\Category\BlogCategory|\App\Model\Category\Category|\App\Model\Product\Brand\Brand|\App\Model\Store\Store|\App\Model\CategorySeo\ReadyCategorySeoMix|\App\Model\Product\Flag\Flag|array
     */
    public function resolve(string $slug, ResolveInfo $info)
    {
        $slugWithoutSlash = ltrim($slug, '/');
        $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($this->domain->getId(), $slugWithoutSlash);

        if ($friendlyUrl === null) {
            throw new NoResultFoundForSlugUserError('No result found for request.');
        }

        $routeNameToEntityMap = $this->friendlyUrlRepository->getRouteNameToEntityMap();
        $entity = $routeNameToEntityMap[$friendlyUrl->getRouteName()];

        switch ($entity) {
            case Article::class:
                $article = $this->articleResolver->resolver(null, $slugWithoutSlash);
                $article[SlugResolverMap::SLUG_TYPE] = SlugResolverMap::SLUG_TYPE_ARTICLE;

                return $article;
            case Brand::class:
                /** @var \App\Model\Product\Brand\Brand $brand */
                $brand = $this->brandResolver->resolver(null, $slugWithoutSlash);

                return $brand;
            case BlogArticle::class:
                $blogArticle = $this->blogArticleResolver->resolveByUuidOrUrlSlug(null, $slugWithoutSlash);
                $blogArticle[SlugResolverMap::SLUG_TYPE] = SlugResolverMap::SLUG_TYPE_BLOG_ARTICLE;

                return $blogArticle;
            case BlogCategory::class:
                return $this->blogCategoryResolver->resolveByUuidOrUrlSlug(null, $slugWithoutSlash);
            case Category::class:
                /** @var \App\Model\Category\Category $category */
                $category = $this->categoryResolver->resolveByUuidOrUrlSlug(null, $slugWithoutSlash);
                $matchingReadyCategorySeoMix = $this->findMatchingReadyCategorySeoMix($info, $category);

                return $matchingReadyCategorySeoMix ?? $category;
            case Flag::class:
                return $this->flagResolver->resolveByUuidOrUrlSlug(null, $slugWithoutSlash);
            case Product::class:
                $product = $this->productDetailResolver->resolver(null, $slugWithoutSlash);
                $product[SlugResolverMap::SLUG_TYPE] = SlugResolverMap::SLUG_TYPE_PRODUCT;

                return $product;
            case Store::class:
                return $this->storeResolver->resolver(null, $slugWithoutSlash);
            case ReadyCategorySeoMix::class:
                return $this->readyCategorySeoMixResolver->resolver($slugWithoutSlash);
        }

        throw new NoResultFoundForSlugUserError('No result found for request.');
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'slugResolver',
        ];
    }

    /**
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @param \App\Model\Category\Category $category
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    private function findMatchingReadyCategorySeoMix(ResolveInfo $info, Category $category): ?ReadyCategorySeoMix
    {
        $variableValues = $info->variableValues;

        return $this->readyCategorySeoMixFacade->findReadyCategorySeoMixByQueryInputData(
            $category->getId(),
            $variableValues['filter']['parameters'] ?? [],
            $variableValues['filter']['flags'] ?? [],
            $variableValues['sortingMode'] ?? null
        );
    }
}
