<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

class TransferIssueDataFactory
{
    /**
     * @param string $message
     * @param string $severity
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueData
     */
    public function create(string $message, string $severity): TransferIssueData
    {
        return new TransferIssueData($message, $severity);
    }
}
