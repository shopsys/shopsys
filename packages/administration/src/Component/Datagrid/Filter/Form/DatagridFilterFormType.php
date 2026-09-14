<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterGroupData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The filter form of a datagrid — groups of rules composed by the administrator, sent by GET so that the
 * composed filter lives in the URL. The prototypes of the rules the page swaps in are a form of their own,
 * see `FilterPrototypesFormType`.
 */
final class DatagridFilterFormType extends AbstractType
{
    public const string GROUP_PROTOTYPE_NAME = '__group__';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters */
        $filters = $options['filters'];

        $builder
            ->setMethod('GET')
            ->add('operator', ChoiceType::class, [
                'choices' => [
                    t('all of the groups match') => FilterGroupData::OPERATOR_AND,
                    t('any of the groups matches') => FilterGroupData::OPERATOR_OR,
                ],
                'placeholder' => false,
                'label' => false,
            ])
            ->add('groups', CollectionType::class, [
                'entry_type' => FilterGroupType::class,
                'entry_options' => [
                    'filters' => $filters,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => self::GROUP_PROTOTYPE_NAME,
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
                'data_class' => FilterFormData::class,
                'csrf_protection' => false,
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
                'label' => false,
            ]);
    }
}
