<?php

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setAllowedTypes('label', 'string')
            ->setDefaults([
                'inherit_data' => true,
                'is_group_container_to_render_as_the_last_one' => false,
            ]);
    }
}
