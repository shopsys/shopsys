<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterGroupData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A group of rules of the filter form and the logical operator combining them.
 */
final class FilterGroupType extends AbstractType
{
    public const string RULE_PROTOTYPE_NAME = '__rule__';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('operator', ChoiceType::class, [
                'choices' => [
                    t('all of the rules match') => FilterGroupData::OPERATOR_AND,
                    t('any of the rules matches') => FilterGroupData::OPERATOR_OR,
                ],
                'placeholder' => false,
                'label' => false,
            ])
            ->add('rules', CollectionType::class, [
                'entry_type' => FilterRuleType::class,
                'entry_options' => [
                    'filters' => $options['filters'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => self::RULE_PROTOTYPE_NAME,
                'label' => false,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('filters')
            ->setAllowedTypes('filters', FilterCollection::class)
            ->setDefaults([
                'data_class' => FilterGroupData::class,
                'label' => false,
            ]);
    }
}
