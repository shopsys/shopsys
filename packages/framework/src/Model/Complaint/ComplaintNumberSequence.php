<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\NumberSequence\AbstractNumberSequence;

/**
 * @ORM\Table(name="complaint_number_sequences")
 * @ORM\Entity
 * @phpstan-ignore-next-line // Factory is not implemented as this entity is not supposed to be created in application
 */
class ComplaintNumberSequence extends AbstractNumberSequence
{
}
