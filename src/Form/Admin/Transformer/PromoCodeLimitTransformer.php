<?php

declare(strict_types=1);

namespace App\Form\Admin\Transformer;

use App\Model\Order\PromoCode\PromoCodeLimit;
use App\Model\Order\PromoCode\PromoCodeLimitFactory;
use Symfony\Component\Form\DataTransformerInterface;

class PromoCodeLimitTransformer implements DataTransformerInterface
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitFactory
     */
    private $promoCodeLimitFactory;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeLimitFactory $promoCodeLimitFactory
     */
    public function __construct(PromoCodeLimitFactory $promoCodeLimitFactory)
    {
        $this->promoCodeLimitFactory = $promoCodeLimitFactory;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeLimit $promoCodeLimit
     * @return null|array
     */
    public function transform($promoCodeLimit): ?array
    {
        if ($promoCodeLimit instanceof PromoCodeLimit) {
            return [
                'from' => $promoCodeLimit->getFrom(),
                'percent' => $promoCodeLimit->getPercent(),
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
        return $this->promoCodeLimitFactory->create((string)$value['from'], (string)$value['percent']);
    }
}
