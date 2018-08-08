<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class DuplicateSizeNameException extends Exception implements ImageConfigException
{
    /**
     * @var string|null
     */
    private $sizeName;

    public function __construct(?string $sizeName = null, Exception $previous = null)
    {
        $this->sizeName = $sizeName;

        if ($this->sizeName === null) {
            $message = 'Image size NULL is not unique.';
        } else {
            $message = sprintf('Image size "%s" is not unique.', $this->sizeName);
        }
        parent::__construct($message, 0, $previous);
    }

    public function getSizeName(): ?string
    {
        return $this->sizeName;
    }
}
