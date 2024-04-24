<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

class TransferIssueData
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $severity;

    /**
     * @param string $message
     * @param string $severity
     */
    public function __construct(string $message, string $severity)
    {
        $this->message = $message;
        $this->severity = $severity;
    }
}
