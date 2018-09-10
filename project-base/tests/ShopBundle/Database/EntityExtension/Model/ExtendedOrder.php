<?php

namespace Tests\ShopBundle\Database\EntityExtension\Model;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Order;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 */
class ExtendedOrder extends Order
{

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $stringField;

    /**
     * @return string|null
     */
    public function getStringField()
    {
        return $this->stringField;
    }

    /**
     * @param string|null $stringField
     */
    public function setStringField($stringField)
    {
        $this->stringField = $stringField;
    }
}
