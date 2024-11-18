<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\Packet;

use Shopsys\FrameworkBundle\Model\Order\Order;

class PacketAttributes
{
    protected const int GRAMS_IN_KILOGRAM = 1000;

    protected string $number;

    protected string $name;

    protected string $surname;

    protected string $email;

    protected int $addressId;

    protected float $value;

    protected float $weight;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function __construct(Order $order)
    {
        $this->number = $order->getNumber();
        $this->name = $order->getFirstName();
        $this->surname = $order->getLastName();
        $this->email = $order->getEmail();
        $this->value = (float)$order->getTotalPriceWithVat()->getAmount();
        $this->addressId = (int)$order->getPickupPlaceIdentifier();
        $this->weight = $order->getTotalWeight() > 0 ? $order->getTotalWeight() / static::GRAMS_IN_KILOGRAM : 0;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return int
     */
    public function getAddressId(): int
    {
        return $this->addressId;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }
}
