<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class InputPriceLabelExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    protected $pricingSetting;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     */
    public function __construct(
        PricingSetting $pricingSetting
    ) {
        $this->pricingSetting = $pricingSetting;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('inputPriceLabel', [$this, 'getInputPriceLabel']),
        ];
    }

    /**
     * @return string
     */
    public function getInputPriceLabel()
    {
        $inputPriceType = $this->pricingSetting->getInputPriceType();

        switch ($inputPriceType) {
            case PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT:
                return t('Input price without VAT');

            case PricingSetting::INPUT_PRICE_TYPE_WITH_VAT:
                return t('Input price with VAT');

            default:
                throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException(
                    'Invalid input price type: ' . $inputPriceType
                );
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_price_label_extension';
    }
}
