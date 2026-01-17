<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Transfer\Transfer;

class TransferIssueFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(Transfer $transfer, TransferIssueData $transferIssueData): TransferIssue
    {
        $entityName = $this->entityNameResolver->resolve(TransferIssue::class);

        return new $entityName($transfer, $transferIssueData);
    }
}
