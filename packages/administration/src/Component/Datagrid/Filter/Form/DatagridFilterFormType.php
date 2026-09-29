<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterGroupData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The filter form of a datagrid — groups of rules composed by the administrator, sent by GET so that the
 * composed filter lives in the URL.
 *
 * Besides the groups, the form carries a prototype of the operation and the value for every filter and
 * every arity of its operations. The page swaps them in when the administrator changes the filter or
 * the operation of a rule, so no request is needed for that; the prototypes are never submitted.
 */
final class DatagridFilterFormType extends AbstractType
{
    public const string GROUP_PROTOTYPE_NAME = '__group__';

    public const string PROTOTYPES_NAME = 'prototypes';

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
            ])
            ->add($this->createPrototypes($builder, $filters));
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

    /**
     * `prototypes[<filter>][<arity>]` holds the operation select and the value input of the filter for the
     * operations of that arity. It is not mapped, so it never touches the data, and it is never validated —
     * the page keeps it out of the submission, so its fields arrive empty whether the filter is used or not.
     */
    private function createPrototypes(FormBuilderInterface $builder, FilterCollection $filters): FormBuilderInterface
    {
        $prototypes = $builder->create(self::PROTOTYPES_NAME, FormType::class, [
            'mapped' => false,
            'label' => false,
            'validation_groups' => false,
        ]);

        foreach ($filters as $filter) {
            $filterPrototypes = $builder->create($filter->getName(), FormType::class, [
                'mapped' => false,
                'label' => false,
            ]);

            foreach ($this->getOperatorsByArity($filter) as $arity => $operators) {
                $filterPrototypes->add($this->createPrototype($builder, $filter, $arity, $operators));
            }

            $prototypes->add($filterPrototypes);
        }

        return $prototypes;
    }

    /**
     * @param string[] $operators
     */
    private function createPrototype(
        FormBuilderInterface $builder,
        FilterInterface $filter,
        string $arity,
        array $operators,
    ): FormBuilderInterface {
        $prototype = $builder->create($arity, FormType::class, [
            'mapped' => false,
            'label' => false,
        ]);

        $allOperators = $filter->getOperators();
        $prototype->add('operator', ChoiceType::class, [
            'choices' => array_combine(array_map($filter->getOperatorLabel(...), $allOperators), $allOperators),
            'choice_attr' => fn (string $operator): array => [
                'data-arity' => $filter->getValueArity($operator),
            ],
            'data' => $operators[0],
            'placeholder' => false,
            'label' => false,
            'attr' => [
                'data-datagrid-filter-target' => 'operatorSelect',
                'data-action' => 'change->datagrid-filter#changeOperator',
            ],
        ]);

        if ($arity === ExpressionValueArityEnum::RANGE) {
            $prototype->add('value', FilterRangeType::class, [
                'entry_type' => $filter->getValueFormType($operators[0]),
                'entry_options' => $filter->getValueFormOptions($operators[0]),
            ]);
        } elseif ($arity !== ExpressionValueArityEnum::NONE) {
            $prototype->add('value', $filter->getValueFormType($operators[0]), array_replace($filter->getValueFormOptions($operators[0]), ['label' => false]));
        }

        return $prototype;
    }

    /**
     * @return array<string, string[]> Operators of the filter grouped by the arity of their value, in the order they are offered
     */
    private function getOperatorsByArity(FilterInterface $filter): array
    {
        $operatorsByArity = [];

        foreach ($filter->getOperators() as $operator) {
            $operatorsByArity[$filter->getValueArity($operator)][] = $operator;
        }

        return $operatorsByArity;
    }
}
