<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\NumberSequence\AbstractNumberSequence;

/**
 * @ORM\Table(name="order_number_sequences")
 * @ORM\Entity
 * @phpstan-ignore-next-line // Factory is not implemented as this entity is not supposed to be created in application
 */
class OrderNumberSequence extends AbstractNumberSequence
{
}
