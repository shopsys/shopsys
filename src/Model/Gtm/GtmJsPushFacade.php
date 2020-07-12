<?php

declare(strict_types=1);

namespace App\Model\Gtm;

use App\Component\Domain\Domain;
use App\Model\Product\Listed\ListedProductView;
use App\Model\Slider\SliderItem;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class GtmJsPushFacade
{
    /**
     * @var \App\Model\Gtm\GtmContainer
     */
    private $gtmContainer;

    /**
     * @var \App\Model\Gtm\DataLayerMapper
     */
    private $dataLayerMapper;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @param \App\Model\Gtm\GtmContainer $gtmContainer
     * @param \App\Model\Gtm\DataLayerMapper $dataLayerMapper
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        GtmContainer $gtmContainer,
        DataLayerMapper $dataLayerMapper,
        Domain $domain,
        CurrencyFacade $currencyFacade
    ) {
        $this->gtmContainer = $gtmContainer;
        $this->dataLayerMapper = $dataLayerMapper;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @param \App\Model\Slider\SliderItem $sliderItem
     * @param string $positionText
     * @return array
     */
    public function getSliderItemClickData(SliderItem $sliderItem, $positionText): array
    {
        if (!$this->gtmContainer->isEnabled()) {
            return [];
        }

        return [
            'event' => DataLayer::EVENT_NAME_PRODUCT_CLICK,
            'eventData' => [
                'action' => 'Proklik banneru',
                'label' => $sliderItem->getGtmId(),
            ],
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'promoView' => [
                    'promotions' => $this->dataLayerMapper->createDataLayerSliderItemsFromSliderItems([$sliderItem], $positionText),
                ],
            ],
        ];
    }

    /**
     * @param \App\Model\Slider\SliderItem[] $sliderItems
     * @param string $positionText
     * @return array
     */
    public function getSliderScrollData(array $sliderItems, string $positionText): array
    {
        if (!$this->gtmContainer->isEnabled()) {
            return [];
        }

        return [
            'event' => DataLayer::EVENT_NAME_PROMO_VIEW,
            'eventData' => [
                'action' => 'Zobrazení banneru',
            ],
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'promoView' => [
                    'promotions' => $this->dataLayerMapper->createDataLayerSliderItemsFromSliderItems($sliderItems, $positionText),
                ],
            ],
        ];
    }

    /**
     * @param \App\Model\Product\Listed\ListedProductView[] $topProducts
     * @param string $list
     * @return array
     */
    public function getTopProductsScrollData(array $topProducts, $list): array
    {
        if (!$this->gtmContainer->isEnabled()) {
            return [];
        }

        return [
            'event' => DataLayer::EVENT_NAME_PRODUCT_IMPRESSIONS,
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'impressions' => $this->dataLayerMapper->createDataLayerProductsFromListedProductViews(
                    $topProducts,
                    null,
                    $list
                ),
            ],
        ];
    }

    /**
     * @param \App\Model\Product\Listed\ListedProductView $listedProductView
     * @param int $position
     * @param string|null $list
     * @return array
     */
    public function getListedProductViewClickData(ListedProductView $listedProductView, int $position, ?string $list): array
    {
        if (!$this->gtmContainer->isEnabled()) {
            return [];
        }

        return [
            'event' => DataLayer::EVENT_NAME_PRODUCT_CLICK,
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'click' => [
                    'actionField' => [
                        'list' => $list,
                    ],
                    'products' => $this->dataLayerMapper->createDataLayerProductsFromListedProductViews(
                        [$listedProductView],
                        $position,
                        $list
                    ),
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    private function getCurrentDomainDefaultCurrencyCode(): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return $currency->getCode();
    }
}
