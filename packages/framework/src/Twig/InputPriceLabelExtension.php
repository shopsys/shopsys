<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Twig_Extension;
use Twig_SimpleFunction;

class InputPriceLabelExtension extends Twig_Extension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    private $pricingSetting;

    public function __construct(
        PricingSetting $pricingSetting
    ) {
        $this->pricingSetting = $pricingSetting;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('inputPriceLabel', [$this, 'getInputPriceLabel']),
        ];
    }

    public function getInputPriceLabel(): string
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

    public function getName(): string
    {
        return 'input_price_label_extension';
    }
}
