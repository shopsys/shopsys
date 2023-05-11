<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueSlugsOnDomains extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Address {{ url }} already exists.';

    /**
     * @var string
     */
    public $messageDuplicate = 'Address {{ url }} can be entered only once.';
}
