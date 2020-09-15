<?php

declare(strict_types=1);


namespace App\Component\Router\Import;

use Exception;

class SingleImportIrresolvableStringException extends Exception
{
    public function __construct(string $string)
    {
        $message = sprintf('String \'%s\' doesn\'t contain redirect information.', $string);

        parent::__construct($message);
    }
}
