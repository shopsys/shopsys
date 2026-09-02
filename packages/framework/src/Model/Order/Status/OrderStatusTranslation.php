<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[AsMcpInheritedColumn(fieldName: 'id')]
#[AsMcpInheritedColumn(fieldName: 'locale')]
#[ORM\Table(name: 'order_status_translations')]
#[ORM\Entity]
class OrderStatusTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    #[AsMcpColumn]
    #[Prezent\Translatable(targetEntity: OrderStatus::class)]
    #[Override]
    protected $translatable;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $name {
        set {
            $this->name = TransformStringHelper::getTrimmedStringOrNullOnEmpty($value);
        }
    }

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
