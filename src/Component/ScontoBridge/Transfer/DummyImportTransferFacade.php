<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge\Transfer;

use Generator;

class DummyImportTransferFacade extends AbstractScontoBridgeImportTransfer
{
    protected function processItem(array $scontoBridgeData): void
    {
        // TODO: Implement processItem() method.
    }

    protected function doBeforeTransfer(): void
    {
        // TODO: Implement doBeforeTransfer() method.
    }

    protected function doAfterTransfer(): void
    {
        // TODO: Implement doAfterTransfer() method.
    }

    protected function getData(): Generator
    {
        yield '';
    }

    public function getTransferName(): string
    {
        return 'Dummy transfer';
    }

    public function getTransferIdentifier(): string
    {
        return 'dummy_transfer';
    }
}
