<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension\Exception;

use Exception;

class ExtendedClassNamesAlreadySetException extends Exception
{
    public function __construct()
    {
        parent::__construct('The extended class names are set once during kernel boot and cannot be replaced afterwards');
    }
}
