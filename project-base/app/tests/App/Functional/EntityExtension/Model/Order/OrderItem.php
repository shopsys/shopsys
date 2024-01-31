<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\Order;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\App\Functional\EntityExtension\Model\Product\Product;

/**
 * @ORM\Table(name="order_items")
 * @ORM\Entity
 */
class OrderItem
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Tests\App\Functional\EntityExtension\Model\Order\Order", inversedBy="items")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected Order $order;

    /**
     * @ORM\Column(type="text")
     */
    protected string $name;

    /**
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected Money $priceWithoutVat;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected string $vatPercent;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="Tests\App\Functional\EntityExtension\Model\Product\Product")
     * @ORM\JoinColumn(nullable=true, name="product_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected ?Product $product = null;

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\Order\Order $order
     */
    public function __construct(
        Order $order,
    ) {
        $this->order = $order;
        $this->order->addItem($this);
    }

    public function setMandatoryData(): void
    {
        $this->name = 'name';
        $this->priceWithoutVat = Money::create(10);
        $this->vatPercent = '0';
        $this->quantity = 1;
    }
}
