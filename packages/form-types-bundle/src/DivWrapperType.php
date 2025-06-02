<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DivWrapperType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'required' => false,
            'inherit_data' => true,
            'attr' => [],
        ]);
    }
}
