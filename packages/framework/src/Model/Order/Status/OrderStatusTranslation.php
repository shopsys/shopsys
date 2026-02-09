<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

#[ORM\Table(name: 'order_status_translations')]
#[ORM\Entity]
class OrderStatusTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    #[Prezent\Translatable(targetEntity: OrderStatus::class)]
    protected $translatable;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}
