<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The prototypes of the rules of the filter form — `prototypes[<filter>][<arity>]` is a rule of the filter starting
 * with the first operation of that arity, holding the operation select and the value input the page swaps in when
 * the administrator changes the filter or the operation of a rule.
 *
 * A form of its own named as the filter form, so that a prototype is renamed into a rule by its path alone, and
 * never submitted — the composed rules never touch it.
 */
final class FilterPrototypesFormType extends AbstractType
{
    public const string PROTOTYPES_NAME = 'prototypes';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters */
        $filters = $options['filters'];
        $prototypes = $builder->create(self::PROTOTYPES_NAME, FormType::class, [
            'label' => false,
        ]);

        foreach ($filters as $filter) {
            $filterPrototypes = $builder->create($filter->getName(), FormType::class, [
                'label' => false,
            ]);

            foreach ($this->getFirstOperatorsByArity($filter) as $arity => $operator) {
                $ruleData = new FilterRuleData();
                $ruleData->filter = $filter->getName();
                $ruleData->operator = $operator;

                $filterPrototypes->add($arity, FilterRuleType::class, [
                    'filters' => $filters,
                    'data' => $ruleData,
                ]);
            }

            $prototypes->add($filterPrototypes);
        }

        $builder->add($prototypes);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('filters')
            ->setAllowedTypes('filters', FilterCollection::class)
            ->setDefaults([
                'csrf_protection' => false,
                'label' => false,
            ]);
    }

    /**
     * @return array<string, string> The first operation of the filter for every arity of the value, in the order they are offered
     */
    private function getFirstOperatorsByArity(FilterInterface $filter): array
    {
        $firstOperatorsByArity = [];

        foreach ($filter->getOperators() as $operator) {
            $firstOperatorsByArity[$filter->getValueArity($operator)] ??= $operator;
        }

        return $firstOperatorsByArity;
    }
}
