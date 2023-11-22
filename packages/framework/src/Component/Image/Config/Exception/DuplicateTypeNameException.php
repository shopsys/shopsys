<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class DuplicateTypeNameException extends Exception implements ImageConfigException
{
    protected ?string $typeName = null;

    /**
     * @param string|null $typeName
     * @param \Exception|null $previous
     */
    public function __construct(?string $typeName = null, ?Exception $previous = null)
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
    public function getTypeName(): ?string
    {
        return $this->typeName;
    }
}
