<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ComplaintStatusTypeEnum extends AbstractEnum
{
    public const string STATUS_TYPE_NEW = 'new';
    public const string STATUS_TYPE_RESOLVED = 'resolved';
    public const string STATUS_TYPE_IN_PROGRESS = 'in_progress';
}
