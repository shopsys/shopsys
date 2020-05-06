<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Product\Listed\ListedProductViewElasticFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface;

class HomepageController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    private $seoSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade
     */
    private $sliderItemFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface
     */
    private $listedProductViewFacade;

    /**
     * @var \App\Model\Product\Listed\ListedProductViewElasticFacade
     */
    protected $listedProductViewElasticFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade $sliderItemFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface $listedProductViewFacade
     * @param \App\Model\Product\Listed\ListedProductViewElasticFacade $listedProductViewElasticFacade
     */
    public function __construct(
        SliderItemFacade $sliderItemFacade,
        SeoSettingFacade $seoSettingFacade,
        Domain $domain,
        ListedProductViewFacadeInterface $listedProductViewFacade,
        ListedProductViewElasticFacade $listedProductViewElasticFacade
    ) {
        $this->sliderItemFacade = $sliderItemFacade;
        $this->seoSettingFacade = $seoSettingFacade;
        $this->domain = $domain;
        $this->listedProductViewFacade = $listedProductViewFacade;
        $this->listedProductViewElasticFacade = $listedProductViewElasticFacade;
    }

    public function indexAction()
    {
        $sliderItems = $this->sliderItemFacade->getAllVisibleOnCurrentDomain();
        $topProducts = $this->listedProductViewFacade->getAllTop();
        $inSaleListedProducts = $this->listedProductViewElasticFacade->getListedSaleProducts();

        return $this->render('Front/Content/Default/index.html.twig', [
            'sliderItems' => $sliderItems,
            'topProducts' => $topProducts,
            'title' => $this->seoSettingFacade->getTitleMainPage($this->domain->getId()),
            'metaDescription' => $this->seoSettingFacade->getDescriptionMainPage($this->domain->getId()),
            'inSaleProducts' => $inSaleListedProducts,
        ]);
    }
}
