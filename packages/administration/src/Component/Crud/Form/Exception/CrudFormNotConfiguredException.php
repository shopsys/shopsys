<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Form\Exception;

use Exception;

class CrudFormNotConfiguredException extends Exception
{
    public function __construct()
    {
        parent::__construct('Form has not been configured. Call useFormType() or useBuilder() in configureForm() first.');
    }
}
