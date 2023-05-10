<?php

declare(strict_types=1);

namespace App\Model\Transfer\Issue;

class TransferIssueData
{
    /**
     * @var string|null
     */
    public $message;

    /**
     * @var string|null
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
