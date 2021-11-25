<?php

declare(strict_types=1);

namespace App\Component\SsfwccBridge\Transfer;

use Generator;

class DummyImportTransferFacade extends AbstractBridgeImportTransfer
{
    /**
     * @param array $bridgeData
     */
    protected function processItem(array $bridgeData): void
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
