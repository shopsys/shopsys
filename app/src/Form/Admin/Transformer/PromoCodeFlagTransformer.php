<?php

declare(strict_types=1);

namespace App\Form\Admin\Transformer;

use App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory;
use Symfony\Component\Form\DataTransformerInterface;

class PromoCodeFlagTransformer implements DataTransformerInterface
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory
     */
    private PromoCodeFlagFactory $promoCodeFlagFactory;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory $promoCodeFlagFactory
     */
    public function __construct(PromoCodeFlagFactory $promoCodeFlagFactory)
    {
        $this->promoCodeFlagFactory = $promoCodeFlagFactory;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag|null $value
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
     * @return \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag|null
     */
    public function reverseTransform($value): ?PromoCodeFlag
    {
        if (is_array($value) === false || $value['flag'] === null || $value['type'] === null) {
            return null;
        }

        return $this->promoCodeFlagFactory->create(
            $value['flag'],
            $value['type']
        );
    }
}
