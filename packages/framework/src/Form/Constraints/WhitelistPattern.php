<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class WhitelistPattern extends Constraint
{
    public string $message = 'Invalid whitelist pattern.';

    public string $blankMessage = 'Please enter whitelist pattern.';
}
