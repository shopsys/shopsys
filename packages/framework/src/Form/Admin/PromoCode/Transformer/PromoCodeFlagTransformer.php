<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode\Transformer;

use Override;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory;
use Symfony\Component\Form\DataTransformerInterface;

class PromoCodeFlagTransformer implements DataTransformerInterface
{
    public function __construct(
        protected readonly PromoCodeFlagFactory $promoCodeFlagFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag|null $value
     */
    #[Override]
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
     */
    #[Override]
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
