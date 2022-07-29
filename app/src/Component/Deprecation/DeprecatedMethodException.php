<?php

declare(strict_types=1);

namespace App\Component\Deprecation;

use Exception;

class DeprecatedMethodException extends Exception
{
    public function __construct()
    {
        parent::__construct('Method is deprecated.');
    }
}
