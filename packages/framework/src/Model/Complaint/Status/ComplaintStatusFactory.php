<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ComplaintStatusFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTypeEnum $complaintStatusTypeEnum
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly ComplaintStatusTypeEnum $complaintStatusTypeEnum,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $data
     * @param string $statusType
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    public function create(ComplaintStatusData $data, string $statusType): ComplaintStatus
    {
        $this->complaintStatusTypeEnum->validateCase($statusType);
        $entityClassName = $this->entityNameResolver->resolve(ComplaintStatus::class);

        return new $entityClassName($data, $statusType);
    }
}
