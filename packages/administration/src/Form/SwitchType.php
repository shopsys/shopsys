<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwitchType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'label_attr' => [
                'class' => 'checkbox-switch',
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return CheckboxType::class;
    }
}
