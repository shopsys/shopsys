<?php

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisplayOnlyType extends AbstractType
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'mapped' => false,
                'required' => false,
                'disabled' => true,
                'compound' => false,
                'attr' => [
                    'readonly' => 'readonly',
                    'class' => '',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?string
    {
        return FormType::class;
    }
}
