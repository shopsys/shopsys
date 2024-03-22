<?php

declare(strict_types=1);

namespace App\Form\Admin\Transformer;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory;
use Symfony\Component\Form\DataTransformerInterface;

class PromoCodeFlagTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory $promoCodeFlagFactory
     */
    public function __construct(private PromoCodeFlagFactory $promoCodeFlagFactory)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag|null $value
     * @return array|null
     */
    public function transform($value): ?array
    {
        if ($value instanceof PromoCodeFlag) {
            return [
                'flag' => $value->getFlag(),
                'type' => $value->getType(),
            ];
        }

        return null;
    }

    /**
     * @param mixed $value
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag|null
     */
    public function reverseTransform($value): ?PromoCodeFlag
    {
        if (is_array($value) === false || $value['flag'] === null || $value['type'] === null) {
            return null;
        }

        return $this->promoCodeFlagFactory->create(
            $value['flag'],
            $value['type'],
        );
    }
}
