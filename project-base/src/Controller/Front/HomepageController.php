<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface;

class HomepageController extends FrontBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade $sliderItemFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface $listedProductViewFacade
     */
    public function __construct(
        private readonly SliderItemFacade $sliderItemFacade,
        private readonly SeoSettingFacade $seoSettingFacade,
        private readonly Domain $domain,
        private readonly ListedProductViewFacadeInterface $listedProductViewFacade,
    ) {
    }

    public function indexAction()
    {
        $sliderItems = $this->sliderItemFacade->getAllVisibleOnCurrentDomain();
        $topProducts = $this->listedProductViewFacade->getAllTop();

        return $this->render('Front/Content/Default/index.html.twig', [
            'sliderItems' => $sliderItems,
            'topProducts' => $topProducts,
            'title' => $this->seoSettingFacade->getTitleMainPage($this->domain->getId()),
            'metaDescription' => $this->seoSettingFacade->getDescriptionMainPage($this->domain->getId()),
        ]);
    }
}
