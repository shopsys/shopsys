<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress as BaseDeliveryAddress;

/**
 * @ORM\Table(name="delivery_addresses")
 * @ORM\Entity
 * @method edit(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method setData(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 */
class DeliveryAddress extends BaseDeliveryAddress
{
    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    private string $uuid;

    /**
     * @param \App\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    public function __construct(DeliveryAddressData $deliveryAddressData)
    {
        parent::__construct($deliveryAddressData);

        $this->uuid = $deliveryAddressData->uuid ?: Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}
