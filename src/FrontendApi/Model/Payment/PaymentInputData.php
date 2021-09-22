<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Payment;

use Shopsys\FrameworkBundle\Model\Pricing\Price;

class PaymentInputData
{
    /**
     * @var string
     */
    private string $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private Price $price;

    /**
     * @param array $paymentInput
     */
    public function __construct(array $paymentInput)
    {
        $this->uuid = $paymentInput['uuid'];
        $this->price = new Price(
            $paymentInput['price']['priceWithoutVat'],
            $paymentInput['price']['priceWithVat']
        );
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }
}
