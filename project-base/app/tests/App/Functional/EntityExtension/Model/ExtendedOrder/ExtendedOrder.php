<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedOrder;

use Doctrine\ORM\Mapping as ORM;
use Tests\App\Functional\EntityExtension\Model\Order\Order;

#[ORM\Table(name: 'orders')]
#[ORM\Entity]
class ExtendedOrder extends Order
{
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $stringField = null;
}
