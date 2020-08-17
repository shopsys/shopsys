<?php

declare(strict_types=1);


namespace App\Model\Order\PromoCode\Import;

use Exception;

class SingleImportStructureException extends Exception
{
    public function __construct($expectedColumnsCount, $actualColumnsCount)
    {
        $message = sprintf('Wrong columns count in CSV file, expected: %d actual is %d, probably u have wrong format of CSV file, eg.: extra quotation marks as column separators.', $expectedColumnsCount, $actualColumnsCount);

        parent::__construct($message);
    }
}
