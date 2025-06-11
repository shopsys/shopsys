<?php

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseFormType extends AbstractType
{
    // Abstract classes should not be made final
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Some configuration
    }
}
