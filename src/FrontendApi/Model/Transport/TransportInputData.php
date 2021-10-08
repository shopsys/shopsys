<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\Price;

class TransportInputData
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
     * @var string|null
     */
    private ?string $personalPickupStoreUuid;

    /**
     * @param array $transportInput
     */
    public function __construct(array $transportInput)
    {
        $this->uuid = $transportInput['uuid'];
        $this->price = new Price(
            $transportInput['price']['priceWithoutVat'],
            $transportInput['price']['priceWithVat']
        );
        $this->personalPickupStoreUuid = $transportInput['personalPickupStoreUuid'];
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

    /**
     * @return string|null
     */
    public function getPersonalPickupStoreUuid(): ?string
    {
        return $this->personalPickupStoreUuid;
    }
}
