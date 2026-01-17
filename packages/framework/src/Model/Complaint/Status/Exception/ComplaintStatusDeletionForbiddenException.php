<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;

class ComplaintStatusDeletionForbiddenException extends Exception
{
    public function __construct(protected readonly ComplaintStatus $complaintStatus, ?Exception $previous = null)
    {
        parent::__construct('Deletion of complaint status ID = ' . $complaintStatus->getId() . ' is forbidden', 0, $previous);
    }

    public function getComplaintStatus(): ComplaintStatus
    {
        return $this->complaintStatus;
    }
}
