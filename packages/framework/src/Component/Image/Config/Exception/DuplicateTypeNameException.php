<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class DuplicateTypeNameException extends Exception implements ImageConfigException
{
    /**
     * @var string|null
     */
    private $typeName;

    public function __construct(?string $typeName = null, Exception $previous = null)
    {
        $this->typeName = $typeName;

        if ($this->typeName === null) {
            $message = 'Image type NULL is not unique.';
        } else {
            $message = sprintf('Image type "%s" is not unique.', $this->typeName);
        }
        parent::__construct($message, 0, $previous);
    }

    public function getTypeName(): ?string
    {
        return $this->typeName;
    }
}
