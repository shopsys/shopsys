<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A group of checkboxes for selecting days of the week, submitted as ISO-8601 day numbers
 * (1 for Monday through 7 for Sunday)
 */
final class DaysOfWeekType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                t('Monday') => 1,
                t('Tuesday') => 2,
                t('Wednesday') => 3,
                t('Thursday') => 4,
                t('Friday') => 5,
                t('Saturday') => 6,
                t('Sunday') => 7,
            ],
            'choice_translation_domain' => false,
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
