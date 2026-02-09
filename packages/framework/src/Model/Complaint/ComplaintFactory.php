<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;

class ComplaintFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
        protected readonly ComplaintResolutionEnum $complaintResolutionEnum,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem[] $complaintItems
     */
    public function create(
        ComplaintData $complaintData,
        array $complaintItems,
    ): Complaint {
        if ($complaintData->status === null) {
            $complaintData->status = $this->complaintStatusFacade->getDefault();
        }

        $entityClassName = $this->entityNameResolver->resolve(Complaint::class);

        if (!$this->complaintResolutionEnum->isMoneyReturn($complaintData->resolution)) {
            $complaintData->bankAccountNumber = null;
        }

        return new $entityClassName($complaintData, $complaintItems);
    }
}
