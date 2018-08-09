<?php

namespace Shopsys\FrameworkBundle\Command\Exception;

use Exception;
use Throwable;

class MissingLocaleException extends Exception
{
    /**
     * @var string
     */
    private $locale;

    public function __construct($missingLocale, Throwable $previous = null)
    {
        $message = sprintf(
            'It looks like your operating system does not support locale "%s". '
                . 'Please visit docs/installation/native-installation.md#troubleshooting for more details.',
            $missingLocale
        );

        $this->locale = $missingLocale;

        parent::__construct($message, 0, $previous);
    }

    public function getLocale()
    {
        return $this->locale;
    }
}
