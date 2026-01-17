<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Contracts\Translation\TranslatorInterface;

final class YesNoType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                $this->translator->trans('Yes') => true,
                $this->translator->trans('No') => false,
            ],
            'choice_translation_domain' => false,
            'choice_name' => function ($choice) {
                return $choice ? 'yes' : 'no';
            },
            'multiple' => false,
            'expanded' => true,
            'required' => true,
            'placeholder' => false,
            'constraints' => [
                new Constraints\NotNull(
                    message: 'Please choose at least one option',
                ),
            ],
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
