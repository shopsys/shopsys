<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NumberSequence;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractNumberSequence
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=false)
     */
    protected $number;

    /**
     * @param int $id
     * @param string $number
     */
    public function __construct($id, $number = '0')
    {
        $this->id = $id;
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }
}
