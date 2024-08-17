<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;

class ComplaintFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade $complaintStatusFacade
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData $complaintData
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem[] $complaintItems
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function create(
        ComplaintData $complaintData,
        array $complaintItems,
    ): Complaint {
        if ($complaintData->status === null) {
            $complaintData->status = $this->complaintStatusFacade->getDefault();
        }

        $entityClassName = $this->entityNameResolver->resolve(Complaint::class);

        return new $entityClassName($complaintData, $complaintItems);
    }
}
