<?php

declare(strict_types=1);

namespace App\Form\Front\Product;

use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterFacade;
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
     * @var \App\Model\Product\Parameter\ParameterValue[][]
     */
    private $booleanParameterValuesIndexedByLocaleAndText;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    private $parameterChoicesIndexedByParameterId;

    /**
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(ParameterFacade $parameterFacade)
    {
        $this->booleanParameterValuesIndexedByLocaleAndText = $parameterFacade->getListBooleanParameterValuesIndexedByLocaleAndText();
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
        $config = $options['product_filter_config'];

        $this->parameterChoicesIndexedByParameterId = [];
        foreach ($config->getParameterChoices() as $parameterFilterChoice) {

            /** @var \App\Model\Product\Parameter\Parameter $parameter */
            $parameter = $parameterFilterChoice->getParameter();

            /** @var \App\Model\Product\Parameter\ParameterValue[] $parameterValues */
            $parameterValues = $parameterFilterChoice->getValues();

            $this->parameterChoicesIndexedByParameterId[$parameter->getId()] = $parameterFilterChoice;

            if ($parameter->getAkeneoType() === Parameter::AKENEO_ATTRIBUTES_TYPE_BOOLEAN) {
                $parameterValues = $this->prepareYesNoParameterValues($parameterValues);
            }

            if ($parameter->getParameterUnit() !== null) {
                $parameterValues = $this->prepareParameterUnitsForParameterValues($parameter, $parameterValues);
            }

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
     * @param \App\Model\Product\Parameter\ParameterValue[] $parameterValues
     * @return  \App\Model\Product\Parameter\ParameterValue[]
     */
    private function prepareYesNoParameterValues(array $parameterValues): array
    {
        if (count($parameterValues) === 1) {
            $parameterValue = reset($parameterValues);
            $parameterTextValues = array_keys($this->booleanParameterValuesIndexedByLocaleAndText[$parameterValue->getLocale()]);
            $parameterTextValues = array_combine($parameterTextValues, $parameterTextValues);

            unset($parameterTextValues[$parameterValue->getText()]);
            $parameterTextValue = reset($parameterTextValues);

            $parameterValues[] = $this->booleanParameterValuesIndexedByLocaleAndText[$parameterValue->getLocale()][$parameterTextValue];
        }

        return $parameterValues;
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\Model\Product\Parameter\ParameterValue[] $parameterValues
     * @return array
     */
    private function prepareParameterUnitsForParameterValues(
        Parameter $parameter,
        array $parameterValues
    ): array {
        $newParameterValues = [];
        foreach ($parameterValues as $parameterValue) {
            $newParameterValue = new \stdClass();
            $newParameterValue->id = $parameterValue->getId();
            $newParameterValue->text = $parameterValue->getText() . ' ' . $parameter->getParameterUnit()->getName();
            $newParameterValues[] = $newParameterValue;
        }

        return $newParameterValues;
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
