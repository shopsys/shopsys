<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Transfer\Transfer;

class TransferIssueFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Transfer $transfer
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueData $transferIssueData
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssue
     */
    public function create(Transfer $transfer, TransferIssueData $transferIssueData): TransferIssue
    {
        $entityName = $this->entityNameResolver->resolve(TransferIssue::class);

        return new $entityName($transfer, $transferIssueData);
    }
}
