<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisplayOnlyType extends AbstractType
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
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
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return FormType::class;
    }
}
