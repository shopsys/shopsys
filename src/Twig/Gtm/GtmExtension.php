<?php

declare(strict_types=1);

namespace App\Twig\Gtm;

use App\Model\Gtm\DataLayer;
use App\Model\Gtm\GtmJsPushFacade;
use App\Model\Product\Listed\ListedProductView;
use App\Model\Slider\SliderItem;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GtmExtension extends AbstractExtension
{
    /**
     * @var \Twig\Environment
     */
    private $twigEnvironment;

    /**
     * @var \App\Model\Gtm\GtmJsPushFacade
     */
    private $gtmJsPushFacade;

    /**
     * @param \Twig\Environment $twigEnvironment
     * @param \App\Model\Gtm\GtmJsPushFacade $gtmJsPushFacade
     */
    public function __construct(
        Environment $twigEnvironment,
        GtmJsPushFacade $gtmJsPushFacade
    ) {
        $this->twigEnvironment = $twigEnvironment;
        $this->gtmJsPushFacade = $gtmJsPushFacade;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('gtm', [$this, 'getGtmCode'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param mixed $element
     * @param int $position
     * @param array|null $attributes
     * @return string
     */
    public function getGtmCode($element, int $position, ?array $attributes = null): string
    {
        if ($element instanceof ListedProductView) {
            $list = $attributes['list'] ?? null;
            $gtm = $this->gtmJsPushFacade->getListedProductViewClickData($element, (int)$position, $list);
        } elseif ($element instanceof SliderItem) {
            $gtm = $this->gtmJsPushFacade->getSliderItemClickData(
                $element,
                DataLayer::HOMEPAGE_SLIDER_LABEL,
                $position
            );
        } else {
            throw new \App\Twig\Gtm\Exception\InvalidProductObjectTypeException(get_class($element));
        }

        return $this->twigEnvironment->render(
            'Front/Inline/MeasuringScript/Gtm/event.html.twig',
            [
                'gtmEvent' => $gtm,
            ]
        );
    }
}
