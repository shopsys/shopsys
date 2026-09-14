<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * One rule of the filter form: the filter, its operation and the value. The operation and the value depend
 * on the chosen filter (and the value on the arity of the operation), so they are added once the filter is
 * known — from the data when the form is built, from the request when it is submitted.
 */
final class FilterRuleType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters */
        $filters = $options['filters'];
        $filterChoices = [];

        foreach ($filters as $filter) {
            $filterChoices[$filter->getLabel()] = $filter->getName();
        }

        $builder->add('filter', ChoiceType::class, [
            'choices' => $filterChoices,
            'placeholder' => false,
            'label' => false,
            'attr' => [
                'data-datagrid-filter-target' => 'filterSelect',
                'data-action' => 'change->datagrid-filter#changeFilter',
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($filters): void {
            /** @var \Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData|null $data */
            $data = $event->getData();
            $this->addDependentFields($event->getForm(), $filters, $data?->filter, $data?->operator);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($filters): void {
            $data = is_array($event->getData()) ? $event->getData() : [];
            $this->addDependentFields($event->getForm(), $filters, $data['filter'] ?? null, $data['operator'] ?? null);
        });
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('filters')
            ->setAllowedTypes('filters', FilterCollection::class)
            ->setDefaults([
                'data_class' => FilterRuleData::class,
                'label' => false,
                // the value of an operation comparing nothing has no field, a stale value in the request is not an error
                'allow_extra_fields' => true,
            ]);
    }

    /**
     * An unknown filter or operation falls back to the first one offered, the same way the form starts.
     */
    private function addDependentFields(
        FormInterface $form,
        FilterCollection $filters,
        ?string $filterName,
        ?string $operator,
    ): void {
        $filter = $filterName !== null && $filters->has($filterName) ? $filters->get($filterName) : $filters->first();

        if ($filter === null) {
            return;
        }

        $operators = $filter->getOperators();
        $operator = in_array($operator, $operators, true) ? $operator : $operators[0];

        $form->add('operator', ChoiceType::class, [
            'choices' => array_combine(array_map($filter->getOperatorLabel(...), $operators), $operators),
            'choice_attr' => static fn (string $operator): array => ['data-arity' => $filter->getValueArity($operator)],
            'placeholder' => false,
            'label' => false,
            'attr' => [
                'data-datagrid-filter-target' => 'operatorSelect',
                'data-action' => 'change->datagrid-filter#changeOperator',
            ],
        ]);

        if ($form->has('value')) {
            $form->remove('value');
        }

        $this->addValueField($form, $filter, $operator);
    }

    private function addValueField(FormInterface $form, FilterInterface $filter, string $operator): void
    {
        $arity = $filter->getValueArity($operator);

        if ($arity === ExpressionValueArityEnum::NONE) {
            return;
        }

        if ($arity === ExpressionValueArityEnum::RANGE) {
            $form->add('value', FilterRangeType::class, [
                'entry_type' => $filter->getValueFormType($operator),
                'entry_options' => $filter->getValueFormOptions($operator),
            ]);

            return;
        }

        $form->add('value', $filter->getValueFormType($operator), array_replace($filter->getValueFormOptions($operator), ['label' => false]));
    }
}
