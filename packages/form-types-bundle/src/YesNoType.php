<?php

namespace Shopsys\FormTypesBundle;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class YesNoType extends AbstractType
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
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

    /**
     * @return ?string
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
