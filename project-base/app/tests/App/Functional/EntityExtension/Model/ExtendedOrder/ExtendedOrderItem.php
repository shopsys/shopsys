<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedOrder;

use Doctrine\ORM\Mapping as ORM;
use Tests\App\Functional\EntityExtension\Model\Order\OrderItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="order_items")
 */
class ExtendedOrderItem extends OrderItem
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $stringField;

    /**
     * @return string|null
     */
    public function getStringField(): ?string
    {
        return $this->stringField;
    }

    /**
     * @param string|null $stringField
     */
    public function setStringField(?string $stringField): void
    {
        $this->stringField = $stringField;
    }
}
