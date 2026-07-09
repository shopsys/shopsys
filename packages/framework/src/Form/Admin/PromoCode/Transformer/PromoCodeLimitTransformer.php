<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode\Transformer;

use Override;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Form\DataTransformerInterface;

class PromoCodeLimitTransformer implements DataTransformerInterface
{
    public function __construct(
        protected PromoCodeLimitFactory $promoCodeLimitFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly string $currencyCode = '',
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit|null $promoCodeLimit
     */
    #[Override]
    public function transform($promoCodeLimit): ?array
    {
        if ($promoCodeLimit instanceof PromoCodeLimit) {
            return [
                'fromPrice' => $promoCodeLimit->getFromPrice(),
                'discount' => $promoCodeLimit->getDiscount(),
            ];
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    #[Override]
    public function reverseTransform($value): PromoCodeLimit
    {
        $currency = $this->currencyFacade->getByCode($this->currencyCode);

        if (!is_array($value)) {
            return $this->promoCodeLimitFactory->create('0', '0', $currency);
        }

        $fromPrice = $value['fromPrice'] ?? null;
        $discount = $value['discount'] ?? null;

        if ($fromPrice === null || $discount === null) {
            return $this->promoCodeLimitFactory->create('0', '0', $currency);
        }

        return $this->promoCodeLimitFactory->create((string)$fromPrice, (string)$discount, $currency);
    }
}
