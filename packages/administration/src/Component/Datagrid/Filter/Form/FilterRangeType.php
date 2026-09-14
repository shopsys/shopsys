<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * The pair of bounds of an operation comparing a range (`between`) — two inputs of the value type of the filter.
 * A range missing one of its bounds is an error, it would narrow nothing and list more than the administrator asked for.
 */
final class FilterRangeType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entryOptions = array_replace($options['entry_options'], ['required' => false]);

        $builder
            ->add('from', $options['entry_type'], array_replace($entryOptions, ['label' => t('From')]))
            ->add('to', $options['entry_type'], array_replace($entryOptions, ['label' => t('To')]));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('entry_type')
            ->setAllowedTypes('entry_type', 'string')
            ->setDefaults([
                'entry_options' => [],
                'label' => false,
                'constraints' => [new Callback(callback: $this->validateBothBoundsFilled(...))],
            ])
            ->setAllowedTypes('entry_options', 'array');
    }

    /**
     * A range missing both bounds is a rule without a value, which narrows nothing as any other.
     */
    private function validateBothBoundsFilled(mixed $bounds, ExecutionContextInterface $context): void
    {
        $filledBounds = is_array($bounds) ? array_filter(
            [$bounds['from'] ?? null, $bounds['to'] ?? null],
            static fn (mixed $bound): bool => $bound !== null && $bound !== '',
        ) : [];

        if (count($filledBounds) !== 1) {
            return;
        }

        $context->buildViolation(t('Fill in both bounds of the range.', [], Translator::VALIDATOR_TRANSLATION_DOMAIN))
            ->addViolation();
    }
}
