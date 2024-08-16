<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ComplaintStatusFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusEnum $complaintStatusEnum
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly ComplaintStatusEnum $complaintStatusEnum,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $data
     * @param string $status
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    public function create(ComplaintStatusData $data, string $status): ComplaintStatus
    {
        $this->complaintStatusEnum->validateCase($status);
        $entityClassName = $this->entityNameResolver->resolve(ComplaintStatus::class);

        return new $entityClassName($data, $status);
    }
}
