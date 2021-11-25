<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo\Exception;

use Exception;
use Throwable;

class FileSaveFailedException extends Exception
{
    /**
     * @param string $reason
     * @param string $dictionary
     * @param string $fileName
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $reason, string $dictionary, string $fileName, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf('File save failed - reason: "%s" , Dictionary: "%s", FileName: "%s"', $reason, $dictionary, $fileName);

        parent::__construct($message, $code, $previous);
    }
}
