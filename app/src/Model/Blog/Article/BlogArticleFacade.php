<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

use App\Component\Image\ImageFacade;
use App\Component\Placeholder\Placeholder\ProductsSkuPlaceholder;
use App\Model\Blog\Article\Elasticsearch\BlogArticleExportScheduler;
use App\Model\Blog\BlogVisibilityRecalculationScheduler;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Product\ProductFacade;
use App\Twig\Cache\TwigCacheFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class BlogArticleFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Blog\Article\BlogArticleRepository
     */
    private $blogArticleRepository;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @var \App\Model\Blog\Article\BlogArticleFactory
     */
    private $blogArticleFactory;

    /**
     * @var \App\Model\Blog\Article\BlogArticleBlogCategoryDomainFactory
     */
    private $blogArticleBlogCategoryDomainFactory;

    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \App\Model\Blog\BlogVisibilityRecalculationScheduler
     */
    private $blogVisibilityRecalculationScheduler;

    /**
     * @var \App\Twig\Cache\TwigCacheFacade
     */
    private $twigCacheFacade;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private ProductFacade $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Blog\Article\Elasticsearch\BlogArticleExportScheduler
     */
    private BlogArticleExportScheduler $blogArticleExportScheduler;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Blog\Article\BlogArticleFactory $blogArticleFactory
     * @param \App\Model\Blog\Article\BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Model\Blog\BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler
     * @param \App\Twig\Cache\TwigCacheFacade $twigCacheFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleExportScheduler $blogArticleExportScheduler
     */
    public function __construct(
        EntityManagerInterface $em,
        BlogArticleRepository $blogArticleRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        BlogArticleFactory $blogArticleFactory,
        BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory,
        ImageFacade $imageFacade,
        BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler,
        TwigCacheFacade $twigCacheFacade,
        ProductFacade $productFacade,
        Domain $domain,
        BlogArticleExportScheduler $blogArticleExportScheduler
    ) {
        $this->em = $em;
        $this->blogArticleRepository = $blogArticleRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->blogArticleFactory = $blogArticleFactory;
        $this->blogArticleBlogCategoryDomainFactory = $blogArticleBlogCategoryDomainFactory;
        $this->imageFacade = $imageFacade;
        $this->blogVisibilityRecalculationScheduler = $blogVisibilityRecalculationScheduler;
        $this->twigCacheFacade = $twigCacheFacade;
        $this->productFacade = $productFacade;
        $this->domain = $domain;
        $this->blogArticleExportScheduler = $blogArticleExportScheduler;
    }

    /**
     * @param int $blogArticleId
     * @return \App\Model\Blog\Article\BlogArticle|null
     */
    public function findById(int $blogArticleId): ?BlogArticle
    {
        return $this->blogArticleRepository->findById($blogArticleId);
    }

    /**
     * @param int $blogArticleId
     * @return \App\Model\Blog\Article\BlogArticle
     */
    public function getById(int $blogArticleId): BlogArticle
    {
        return $this->blogArticleRepository->getById($blogArticleId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $blogArticleId
     * @return \App\Model\Blog\Article\BlogArticle
     */
    public function getVisibleOnDomainById(DomainConfig $domainConfig, int $blogArticleId): BlogArticle
    {
        return $this->blogArticleRepository->getVisibleOnDomainById($domainConfig, $blogArticleId);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllArticlesCountByDomainId(int $domainId): int
    {
        return $this->blogArticleRepository->getAllBlogArticlesCountByDomainId($domainId);
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticleData $blogArticleData
     * @throws \App\Twig\Cache\Exception\InvalidCacheLifetimeException
     * @return \App\Model\Blog\Article\BlogArticle
     */
    public function create(BlogArticleData $blogArticleData): BlogArticle
    {
        $blogArticleData->products = $this->getProductFromDescription($blogArticleData->descriptions);
        $blogArticle = $this->blogArticleFactory->create($blogArticleData);

        $this->em->persist($blogArticle);
        $this->em->flush();

        $blogArticle->setCategories($this->blogArticleBlogCategoryDomainFactory, $blogArticleData->blogCategoriesByDomainId);
        $blogArticle->createDomains($blogArticleData);

        $this->friendlyUrlFacade->createFriendlyUrls('front_blogarticle_detail', $blogArticle->getId(), $blogArticle->getNames());

        $this->imageFacade->uploadImage($blogArticle, $blogArticleData->image->uploadedFiles, null);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->em->flush();

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->twigCacheFacade->invalidateByKey($this->twigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE, $domainId);
        }

        $this->blogArticleExportScheduler->scheduleRowIdForImmediateExport($blogArticle->getId());

        return $blogArticle;
    }

    /**
     * @param int $blogArticleId
     * @param \App\Model\Blog\Article\BlogArticleData $blogArticleData
     * @throws \App\Twig\Cache\Exception\InvalidCacheLifetimeException
     * @return \App\Model\Blog\Article\BlogArticle
     */
    public function edit(int $blogArticleId, BlogArticleData $blogArticleData): BlogArticle
    {
        $blogArticle = $this->blogArticleRepository->getById($blogArticleId);
        $blogArticleData->products = $this->getProductFromDescription($blogArticleData->descriptions);
        $blogArticle->edit($blogArticleData, $this->blogArticleBlogCategoryDomainFactory);

        $this->em->flush();

        $this->friendlyUrlFacade->saveUrlListFormData('front_blogarticle_detail', $blogArticle->getId(), $blogArticleData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_blogarticle_detail', $blogArticleId, $blogArticle->getNames());

        $this->imageFacade->uploadImage($blogArticle, $blogArticleData->image->uploadedFiles, null);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->em->flush();

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->twigCacheFacade->invalidateByKey($this->twigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE, $domainId);
        }

        $this->blogArticleExportScheduler->scheduleRowIdForImmediateExport($blogArticle->getId());

        return $blogArticle;
    }

    /**
     * @param int $blogArticleId
     * @throws \App\Twig\Cache\Exception\InvalidCacheLifetimeException
     */
    public function delete(int $blogArticleId): void
    {
        $blogArticle = $this->blogArticleRepository->getById($blogArticleId);

        $this->em->remove($blogArticle);
        $this->blogVisibilityRecalculationScheduler->scheduleRecalculation();
        $this->em->flush();

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->twigCacheFacade->invalidateByKey($this->twigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE, $domainId);
        }

        $this->blogArticleExportScheduler->scheduleRowIdForImmediateExport($blogArticleId);
    }

    /**
     * @param int $domainId
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->blogArticleRepository->getAllByDomainId($domainId);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     * @param string $locale
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultForListableInBlogCategory(
        BlogCategory $blogCategory,
        int $domainId,
        string $locale,
        int $page,
        int $limit
    ): PaginationResult {
        return $this->blogArticleRepository->getPaginationResultForListableInBlogCategory($blogCategory, $domainId, $locale, $page, $limit);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $limit
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getHomepageBlogArticlesByDomainId(int $domainId, string $locale, int $limit): array
    {
        return $this->blogArticleRepository->getHomepageBlogArticlesByDomainId($domainId, $locale, $limit);
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticle $blogArticle
     * @param int $domainId
     * @return \App\Model\Blog\Category\BlogCategory
     */
    public function findBlogArticleMainCategoryOnDomain(BlogArticle $blogArticle, int $domainId): ?BlogCategory
    {
        return $this->blogArticleRepository->findBlogArticleMainCategoryOnDomain($blogArticle, $domainId);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param string $locale
     * @param int $limit
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getVisibleByProduct(Product $product, int $domainId, string $locale, int $limit): array
    {
        return $this->blogArticleRepository->getVisibleByProduct($product, $domainId, $locale, $limit);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getByProduct(Product $product, string $locale): array
    {
        return $this->blogArticleRepository->getByProduct($product, $locale);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return string[]
     */
    public function getAllBlogArticlesNamesIndexedByIdByDomainId(int $domainId, string $locale): array
    {
        return $this->blogArticleRepository->getAllBlogArticlesNamesIndexedByIdByDomainId($domainId, $locale);
    }

    /**
     * @param array $descriptions
     * @return \App\Model\Product\Product[]
     */
    private function getProductFromDescription(array $descriptions): array
    {
        $productsCatnums = [];

        foreach ($descriptions as $description) {
            $productsCatnums = array_merge(
                $productsCatnums,
                $this->getProductCatnumsFromDescription($description)
            );
        }

        return $this->productFacade->findAllByCatnums($productsCatnums);
    }

    /**
     * @param string|null $description
     * @return string[]
     */
    public function getProductCatnumsFromDescription(?string $description): array
    {
        if ($description === null) {
            return [];
        }

        $productsCatnums = [];

        $matches = [];
        preg_match_all(ProductsSkuPlaceholder::PATTERN, $description, $matches);

        foreach ($matches['catnums'] as $catnumsString) {
            $matchesCatnums = explode(',', $catnumsString);
            $productsCatnums = array_merge($productsCatnums, $matchesCatnums);
        }

        return $productsCatnums;
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     * @param string $locale
     * @return int[]
     */
    public function getBlogArticleIdsByCategory(BlogCategory $blogCategory, int $domainId, string $locale): array
    {
        return $this->blogArticleRepository->getBlogArticleIdsByCategory(
            $blogCategory,
            $domainId,
            $locale
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getAllVisibleOnDomain(DomainConfig $domainConfig): array
    {
        return $this->blogArticleRepository->getAllVisibleOnDomain($domainConfig);
    }
}
