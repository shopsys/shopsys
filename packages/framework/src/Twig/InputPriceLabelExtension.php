<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class InputPriceLabelExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     */
    public function __construct(
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('inputPriceLabel', [$this, 'getInputPriceLabel']),
        ];
    }

    /**
     * @return string
     */
    public function getInputPriceLabel(): string
    {
        $inputPriceType = $this->pricingSetting->getInputPriceType();

        switch ($inputPriceType) {
            case PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT:
                return t('Input price without VAT');

            case PricingSetting::INPUT_PRICE_TYPE_WITH_VAT:
                return t('Input price with VAT');

            default:
                throw new InvalidInputPriceTypeException(
                    'Invalid input price type: ' . $inputPriceType,
                );
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'input_price_label_extension';
    }
}
