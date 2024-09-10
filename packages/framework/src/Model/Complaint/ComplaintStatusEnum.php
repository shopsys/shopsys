<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ComplaintStatusEnum extends AbstractEnum
{
    public const string STATUS_NEW = 'new';
    public const string STATUS_RESOLVED = 'resolved';
}
