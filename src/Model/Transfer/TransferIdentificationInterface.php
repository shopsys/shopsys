<?php

declare(strict_types=1);

namespace App\Model\Transfer;

interface TransferIdentificationInterface
{
    /**
     * @return string
     */
    public function getTransferName(): string;

    /**
     * @return string
     */
    public function getTransferIdentifier(): string;

    /**
     * @return string
     */
    public function getServiceIdentifier(): string;
}
