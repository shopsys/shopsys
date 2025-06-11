<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class YesNoType extends AbstractType
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
            'placeholder' => false,
        ]);
    }

    /**
     * @return string|null
     */
    #[Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
