<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueCollection extends Constraint
{
    public string $message = 'Values are duplicate.';

    /**
     * @var mixed[]|null
     */
    public ?array $fields = null;

    public bool $allowEmpty = false;
}
