<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Blog\Article\BlogArticleFacade;
use App\Model\Blog\Category\BlogCategoryFacade;
use App\Model\Product\Filter\ProductVariantFilterFacade;
use App\Model\Product\Listed\ListedProductViewElasticFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface;
use Symfony\Component\HttpFoundation\Response;

class HomepageController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    private $seoSettingFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Slider\SliderItemFacade
     */
    private $sliderItemFacade;

    /**
     * @var \App\Model\Product\Listed\ListedProductViewElasticFacade
     */
    private $listedProductViewElasticFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface
     */
    private $listedProductViewFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade
     */
    private $topCategoryFacade;

    /**
     * @var \App\Model\Product\Filter\ProductVariantFilterFacade
     */
    private ProductVariantFilterFacade $productVariantFilterFacade;

    /**
     * @var \App\Model\Blog\Article\BlogArticleFacade
     */
    private $blogArticleFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Slider\SliderItemFacade $sliderItemFacade
     * @param \App\Model\Product\Listed\ListedProductViewElasticFacade $listedProductViewElasticFacade
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface $listedProductViewFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade $topCategoryFacade
     * @param \App\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \App\Model\Product\Filter\ProductVariantFilterFacade $productVariantFilterFacade
     */
    public function __construct(
        SeoSettingFacade $seoSettingFacade,
        Domain $domain,
        SliderItemFacade $sliderItemFacade,
        ListedProductViewElasticFacade $listedProductViewElasticFacade,
        ListedProductViewFacadeInterface $listedProductViewFacade,
        TopCategoryFacade $topCategoryFacade,
        BlogArticleFacade $blogArticleFacade,
        ProductVariantFilterFacade $productVariantFilterFacade
    ) {
        $this->seoSettingFacade = $seoSettingFacade;
        $this->domain = $domain;
        $this->sliderItemFacade = $sliderItemFacade;
        $this->listedProductViewElasticFacade = $listedProductViewElasticFacade;
        $this->topCategoryFacade = $topCategoryFacade;
        $this->blogArticleFacade = $blogArticleFacade;
        $this->productVariantFilterFacade = $productVariantFilterFacade;
        $this->listedProductViewFacade = $listedProductViewFacade;
    }

    public function indexAction()
    {
        return $this->render('Front/Content/Default/index.html.twig', [
            'title' => $this->seoSettingFacade->getTitleMainPage($this->domain->getId()),
            'metaDescription' => $this->seoSettingFacade->getDescriptionMainPage($this->domain->getId()),
        ]);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function slightlyChangingPartsOnHomepageAction(BlogCategoryFacade $blogCategoryFacade): Response
    {
        $sliderItems = $this->sliderItemFacade->getAllVisibleOnCurrentDomain();
        $topProducts = $this->listedProductViewFacade->getAllTop();
        $this->productVariantFilterFacade->setupDefaultVariantsInListedProductViews($topProducts);

        $inSaleListedProducts = $this->listedProductViewElasticFacade->getListedSaleProducts();
        $this->productVariantFilterFacade->setupDefaultVariantsInListedProductViews($inSaleListedProducts);
        $mainCategory = $blogCategoryFacade->getVisibleOnDomainById($this->domain->getId(), BlogArticleController::MAIN_BLOG_CATEGORY_ID);

        return $this->render('Front/Content/Default/slightlyChangingPartsOnHomePage.html.twig', [
            'sliderItems' => $sliderItems,
            'inSaleProducts' => $inSaleListedProducts,
            'categories' => $this->topCategoryFacade->getVisibleCategoriesByDomainId($this->domain->getId()),
            'topProducts' => $topProducts,
            'homepageArticles' => $this->blogArticleFacade->getHomepageBlogArticlesByDomainId(
                $this->domain->getId(),
                $this->domain->getLocale(),
                BlogArticleController::HOMEPAGE_BLOG_ARTICLES
            ),
            'rootCategory' => $mainCategory,
        ]);
    }
}
