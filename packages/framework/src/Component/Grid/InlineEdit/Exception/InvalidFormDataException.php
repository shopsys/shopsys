<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception;

use Exception;

class InvalidFormDataException extends Exception implements InlineEditException
{
    /**
     * @var array
     */
    private $formErrors;

    public function __construct(array $formErrors, Exception $previous = null)
    {
        $this->formErrors = $formErrors;
        parent::__construct('Inline edit form is not valid', 0, $previous);
    }

    public function getFormErrors()
    {
        return $this->formErrors;
    }
}
