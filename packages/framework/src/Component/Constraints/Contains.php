<?php

namespace Shopsys\FrameworkBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Contains extends Constraint
{
    public $message = 'Field must contain {{ needle }}.';
    public $needle = null;

    public function getRequiredOptions()
    {
        return [
            'needle',
        ];
    }
}
