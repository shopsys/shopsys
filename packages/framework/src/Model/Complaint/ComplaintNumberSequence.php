<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\NumberSequence\AbstractNumberSequence;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @phpstan-ignore shopsys.entityShouldHaveFactory
 */
#[AsMcpTable]
#[ORM\Table(name: 'complaint_number_sequences')]
#[ORM\Entity]
class ComplaintNumberSequence extends AbstractNumberSequence
{
}
