<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class InputPriceLabelExtension extends AbstractExtension
{
    public function __construct(
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    /**
     * @return array
     */
    #[Override]
    public function getFunctions()
    {
        return [
            new TwigFunction('inputPriceLabel', $this->getInputPriceLabel(...)),
            new TwigFunction('priceVatLabel', $this->getPriceVatLabel(...)),
        ];
    }

    /**
     * @return string
     */
    public function getInputPriceLabel()
    {
        $inputPriceType = $this->pricingSetting->getInputPriceType();

        switch ($inputPriceType) {
            case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                return t('Input price without VAT');

            case PricingSetting::PRICE_TYPE_WITH_VAT:
                return t('Input price with VAT');

            default:
                throw new InvalidInputPriceTypeException(
                    'Invalid input price type: ' . $inputPriceType,
                );
        }
    }

    public function getPriceVatLabel(): string
    {
        $inputPriceType = $this->pricingSetting->getInputPriceType();

        return match ($inputPriceType) {
            PricingSetting::PRICE_TYPE_WITHOUT_VAT => t('without VAT'),
            PricingSetting::PRICE_TYPE_WITH_VAT => t('with VAT'),
            default => throw new InvalidInputPriceTypeException(
                'Invalid input price type: ' . $inputPriceType,
            ),
        };
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_price_label_extension';
    }
}
