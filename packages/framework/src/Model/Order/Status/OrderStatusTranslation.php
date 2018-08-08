<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="order_status_translations")
 * @ORM\Entity
 */
class OrderStatusTranslation extends AbstractTranslation
{
    /**
     * @Prezent\Translatable(targetEntity="Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus")
     */
    protected $translatable;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
