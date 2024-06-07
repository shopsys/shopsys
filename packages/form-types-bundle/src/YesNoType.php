<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class YesNoType extends AbstractType
{
    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                $this->translator->trans('Yes') => true,
                $this->translator->trans('No') => false,
            ],
            'choice_name' => function ($choice) {
                return $choice ? 'yes' : 'no';
            },
            'multiple' => false,
            'expanded' => true,
            'placeholder' => false,
        ]);
    }

    /**
     * @return string|null
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
