<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class DuplicateTypeNameExceptionInvalid extends InvalidImageConfigException
{
    protected ?string $typeName = null;

    /**
     * @param string|null $typeName
     */
    public function __construct($typeName = null, ?Exception $previous = null)
    {
        $this->typeName = $typeName;

        if ($this->typeName === null) {
            $message = 'Image type NULL is not unique.';
        } else {
            $message = sprintf('Image type "%s" is not unique.', $this->typeName);
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string|null
     */
    public function getTypeName()
    {
        return $this->typeName;
    }
}
