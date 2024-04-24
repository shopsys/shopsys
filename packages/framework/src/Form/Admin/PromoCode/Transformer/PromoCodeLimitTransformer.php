<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode\Transformer;

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
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit
     */
    public function reverseTransform($value): PromoCodeLimit
    {
        if (is_array($value) === false || $value['fromPriceWithVat'] === null || $value['discount'] === null) {
            $this->promoCodeLimitFactory->create('0', '0');
        }

        return $this->promoCodeLimitFactory->create((string)$value['fromPriceWithVat'], (string)$value['discount']);
    }
}
