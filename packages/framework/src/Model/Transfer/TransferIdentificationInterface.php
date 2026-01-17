<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

interface TransferIdentificationInterface
{
    public function getTransferName(): string;

    public function getTransferIdentifier(): string;

    public function getServiceIdentifier(): string;
}
