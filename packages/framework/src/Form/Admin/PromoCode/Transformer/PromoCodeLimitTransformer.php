<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode\Transformer;

use Override;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitFactory;
use Symfony\Component\Form\DataTransformerInterface;

class PromoCodeLimitTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitFactory $promoCodeLimitFactory
     */
    public function __construct(protected PromoCodeLimitFactory $promoCodeLimitFactory)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit|null $promoCodeLimit
     * @return array|null
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
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit
     */
    #[Override]
    public function reverseTransform($value): PromoCodeLimit
    {
        if (!is_array($value)) {
            return $this->promoCodeLimitFactory->create('0', '0');
        }

        $fromPrice = $value['fromPrice'] ?? null;
        $discount = $value['discount'] ?? null;

        if ($fromPrice === null || $discount === null) {
            return $this->promoCodeLimitFactory->create('0', '0');
        }

        return $this->promoCodeLimitFactory->create((string)$fromPrice, (string)$discount);
    }
}
