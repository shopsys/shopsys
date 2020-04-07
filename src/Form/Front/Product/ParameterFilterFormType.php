<?php

declare(strict_types=1);

namespace App\Form\Front\Product;

use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterFilterFormType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    private $parameterChoicesIndexedByParameterId;

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
        $config = $options['product_filter_config'];

        $this->parameterChoicesIndexedByParameterId = [];
        foreach ($config->getParameterChoices() as $parameterChoice) {

            /** @var \App\Model\Product\Parameter\Parameter $parameter */
            $parameter = $parameterChoice->getParameter();

            /** @var \App\Model\Product\Parameter\ParameterValue[] $parameterValues */
            $parameterValues = $parameterChoice->getValues();

            if ($parameter->getParameterUnit() !== null) {
                $newParameterValues = [];
                foreach ($parameterValues as $parameterValue) {
                    $newParameterValue = new \stdClass();
                    $newParameterValue->id = $parameterValue->getId();
                    $newParameterValue->text = $parameterValue->getText() . ' ' . $parameter->getParameterUnit()->getName();
                    $newParameterValues[] = $newParameterValue;
                }
                $parameterValues = $newParameterValues;
            }

            $this->parameterChoicesIndexedByParameterId[$parameter->getId()] = $parameterChoice;

            $builder->add($parameter->getId(), ChoiceType::class, [
                'label' => $parameter->getName(),
                'choices' => $parameterValues,
                'choice_label' => 'text',
                'choice_value' => 'id',
                'choice_name' => 'id',
                'multiple' => true,
                'expanded' => true,
            ]);
        }

        $builder->addViewTransformer($this);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('product_filter_config')
            ->setAllowedTypes('product_filter_config', ProductFilterConfig::class)
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValue[][]|\stdClass[][]|null $value
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[]|null
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            return null;
        }

        $parametersFilterData = [];
        foreach ($value as $parameterId => $parameterValues) {
            if (!array_key_exists($parameterId, $this->parameterChoicesIndexedByParameterId)) {
                continue; // invalid parameter IDs are ignored
            }

            $parameterValuesIndexedByParameterId = [];
            foreach ($this->parameterChoicesIndexedByParameterId[$parameterId]->getValues() as $parameterValue) {
                $parameterValuesIndexedByParameterId[$parameterValue->getId()] = $parameterValue;
            }

            $selectedParameterValues = [];
            foreach ($parameterValues as $parameterValue) {
                if ($parameterValue instanceof \stdClass) {

                    /** @var \stdClass $parameterValue */
                    $selectedParameterValues[] = $parameterValuesIndexedByParameterId[$parameterValue->id];
                } else {
                    $selectedParameterValues[] = $parameterValue;
                }
            }

            $parameterFilterData = new ParameterFilterData();
            $parameterFilterData->parameter = $this->parameterChoicesIndexedByParameterId[$parameterId]->getParameter();
            $parameterFilterData->values = $selectedParameterValues;
            $parametersFilterData[] = $parameterFilterData;
        }

        return $parametersFilterData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[]|null $value
     * @return \App\Model\Product\Parameter\ParameterValue[]|null
     */
    public function transform($value)
    {
        if ($value === null) {
            return null;
        }

        /** @var \App\Model\Product\Parameter\ParameterValue[] $parameterValuesIndexedByParameterId */
        $parameterValuesIndexedByParameterId = [];
        foreach ($value as $parameterFilterData) {
            $parameterId = $parameterFilterData->parameter->getId();
            $parameterValuesIndexedByParameterId[$parameterId] = $parameterFilterData->values;
        }

        return $parameterValuesIndexedByParameterId;
    }
}
