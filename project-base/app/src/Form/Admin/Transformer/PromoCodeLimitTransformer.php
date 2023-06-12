<?php

declare(strict_types=1);

namespace App\Form\Admin\Transformer;

use App\Model\Order\PromoCode\PromoCodeLimit;
use App\Model\Order\PromoCode\PromoCodeLimitFactory;
use Symfony\Component\Form\DataTransformerInterface;

class PromoCodeLimitTransformer implements DataTransformerInterface
{
    /**
     * @param \App\Model\Order\PromoCode\PromoCodeLimitFactory $promoCodeLimitFactory
     */
    public function __construct(private PromoCodeLimitFactory $promoCodeLimitFactory)
    {
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeLimit|null $promoCodeLimit
     * @return array|null
     */
    public function transform($promoCodeLimit): ?array
    {
        if ($promoCodeLimit instanceof PromoCodeLimit) {
            return [
                'fromPriceWithVat' => $promoCodeLimit->getFromPriceWithVat(),
                'discount' => $promoCodeLimit->getDiscount(),
            ];
        }

        return null;
    }

    /**
     * @param mixed $value
     * @return \App\Model\Order\PromoCode\PromoCodeLimit
     */
    public function reverseTransform($value): PromoCodeLimit
    {
        if (is_array($value) === false || $value['fromPriceWithVat'] === null || $value['discount'] === null) {
            $this->promoCodeLimitFactory->create('0', '0');
        }

        return $this->promoCodeLimitFactory->create((string)$value['fromPriceWithVat'], (string)$value['discount']);
    }
}
