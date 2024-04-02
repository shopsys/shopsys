<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\Packet;

use Shopsys\FrameworkBundle\Model\Order\Order;

class PacketAttributes
{
    protected string $number;

    protected string $name;

    protected string $surname;

    protected string $email;

    protected int $addressId;

    protected float $value;

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
}
