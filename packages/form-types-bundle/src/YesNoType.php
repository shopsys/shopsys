<?php

namespace Shopsys\FormTypesBundle;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class YesNoType extends AbstractType
{
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
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

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
