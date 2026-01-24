<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class DuplicateTypeNameExceptionInvalid extends InvalidImageConfigException
{
    public function __construct(protected ?string $typeName = null, ?Exception $previous = null)
    {
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
