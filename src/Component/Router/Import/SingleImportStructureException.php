<?php

declare(strict_types=1);


namespace App\Component\Router\Import;

use Exception;

class SingleImportStructureException extends Exception
{
    /**
     * @param int $expectedColumnsCount
     * @param int $actualColumnsCount
     */
    public function __construct(int $expectedColumnsCount, int $actualColumnsCount)
    {
        $message = sprintf('Wrong columns count in CSV file, expected: %d actual is %d, probably u have wrong format of CSV file, eg.: extra quotation marks as column separators.', $expectedColumnsCount, $actualColumnsCount);

        parent::__construct($message);
    }
}
