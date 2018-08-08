<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="order_number_sequences")
 * @ORM\Entity
 */
class OrderNumberSequence
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=false)
     */
    protected $number;
    
    public function __construct(int $id, string $number = '0')
    {
        $this->id = $id;
        $this->number = $number;
    }

    public function getNumber(): string
    {
        return $this->number;
    }
    
    public function setNumber(string $number): void
    {
        $this->number = $number;
    }
}
