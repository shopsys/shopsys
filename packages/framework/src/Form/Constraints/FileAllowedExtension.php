<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FileAllowedExtension extends Constraint
{
    /**
     * @var string
     */
    public $message = 'File extension {{ value }} is not between allowed extension. Allowed extensions are {{ extensions }}.';

    /**
     * @var array
     */
    public $extensions;

    public function getRequiredOptions()
    {
        return [
            'extensions',
        ];
    }

    public function getDefaultOption()
    {
        return 'extensions';
    }
}
