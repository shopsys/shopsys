<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ComplaintStatusFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly ComplaintStatusTypeEnum $complaintStatusTypeEnum,
    ) {
    }

    public function create(ComplaintStatusData $data, string $statusType): ComplaintStatus
    {
        $this->complaintStatusTypeEnum->validateCase($statusType);
        $entityClassName = $this->entityNameResolver->resolve(ComplaintStatus::class);

        return new $entityClassName($data, $statusType);
    }
}
