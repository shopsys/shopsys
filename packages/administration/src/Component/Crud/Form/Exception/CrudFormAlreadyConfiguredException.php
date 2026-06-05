<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Form\Exception;

use Exception;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormMode;

class CrudFormAlreadyConfiguredException extends Exception
{
    public static function cannotSwitchMode(CrudFormMode $currentMode): self
    {
        return new self(sprintf(
            'Cannot change form configuration mode. The form is already configured in "%s" mode. You cannot combine useFormType() and useBuilder().',
            $currentMode->value,
        ));
    }

    public static function cannotSetOptionAfterBuilderCreated(): self
    {
        return new self('Cannot set form option after the builder has been created. Set form options before calling useBuilder().');
    }
}
