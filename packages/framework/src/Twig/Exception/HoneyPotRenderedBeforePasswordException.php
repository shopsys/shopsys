<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig\Exception;

use Exception;
use Shopsys\FrameworkBundle\Form\HoneyPotType;
use Twig\Error\Error;

class HoneyPotRenderedBeforePasswordException extends Error implements TwigException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(?Exception $previous = null)
    {
        $message = sprintf(
            '%s was rendered before password field.'
            . ' Render honeypot after password field to overcome issues when Firefox prefills input'
            . ' before password with saved username.',
            HoneyPotType::class,
        );

        // let the parent exception guess lineno and filename
        parent::__construct($message, -1, null, $previous);
    }
}
