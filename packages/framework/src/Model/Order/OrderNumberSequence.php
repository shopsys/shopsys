<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\NumberSequence\AbstractNumberSequence;

#[ORM\Table(name: 'order_number_sequences')]
#[ORM\Entity]
class OrderNumberSequence extends AbstractNumberSequence
{
}
