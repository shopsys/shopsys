<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge\Transfer;

use Generator;

class DummyImportTransferFacade extends AbstractScontoBridgeImportTransfer
{
    /**
     * @param array $scontoBridgeData
     */
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

    /**
     * @return \Generator
     */
    protected function getData(): Generator
    {
        yield '';
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return 'Dummy transfer';
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'dummy_transfer';
    }
}
