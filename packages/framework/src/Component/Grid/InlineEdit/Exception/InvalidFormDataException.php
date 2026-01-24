<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception;

use Exception;

class InvalidFormDataException extends Exception
{
    public function __construct(protected readonly array $formErrors, ?Exception $previous = null)
    {
        parent::__construct('Inline edit form is not valid', 0, $previous);
    }

    public function getFormErrors(): array
    {
        return $this->formErrors;
    }
}
