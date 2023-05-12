<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception;

use Exception;

class InvalidFormDataException extends Exception implements InlineEditException
{
    /**
     * @param array $formErrors
     * @param \Exception|null $previous
     */
    public function __construct(protected readonly array $formErrors, ?Exception $previous = null)
    {
        parent::__construct('Inline edit form is not valid', 0, $previous);
    }

    /**
     * @return array
     */
    public function getFormErrors()
    {
        return $this->formErrors;
    }
}
