<?php

declare(strict_types=1);

namespace App\Component\Packetery\Packet;

use App\Model\Order\Order;

class PacketAttributes
{
    private string $number;

    private string $name;

    private string $surname;

    private string $email;

    private int $addressId;

    private float $value;

    /**
     * @param \App\Model\Order\Order $order
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
