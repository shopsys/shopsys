<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The pair of bounds of an operation comparing a range (`between`) — two inputs of the value type of the filter.
 */
final class FilterRangeType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entryOptions = array_replace($options['entry_options'], ['required' => false]);

        $builder
            ->add('from', $options['entry_type'], array_replace($entryOptions, ['label' => t('from')]))
            ->add('to', $options['entry_type'], array_replace($entryOptions, ['label' => t('to')]));
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
            ])
            ->setAllowedTypes('entry_options', 'array');
    }
}
