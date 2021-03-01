<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductParameterValueToProductParameterValuesLocalizedTransformer implements DataTransformerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface
     */
    protected $productParameterValueDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface
     */
    protected $parameterValueDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface $parameterValueDataFactory
     */
    public function __construct(
        ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
        ParameterValueDataFactoryInterface $parameterValueDataFactory
    ) {
        $this->productParameterValueDataFactory = $productParameterValueDataFactory;
        $this->parameterValueDataFactory = $parameterValueDataFactory;
    }

    /**
     * @param mixed $normData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData[]|null
     */
    public function transform($normData)
    {
        if ($normData === null) {
            return null;
        }

        if (!is_array($normData)) {
            throw new TransformationFailedException('Invalid value');
        }

        $normValue = [];
        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData $productParameterValueData */
        foreach ($normData as $productParameterValueData) {
            $parameterId = $productParameterValueData->parameter->getId();
            $locale = $productParameterValueData->parameterValueData->locale;

            if (!array_key_exists($parameterId, $normValue)) {
                $normValue[$parameterId] = new ProductParameterValuesLocalizedData();
                $normValue[$parameterId]->parameter = $productParameterValueData->parameter;
                $normValue[$parameterId]->valueTextsByLocale = [];
            }

            $normValue[$parameterId]->valueTextsByLocale[$locale] = $productParameterValueData->parameterValueData->text;
        }

        return array_values($normValue);
    }

    /**
     * @param mixed $viewData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[]
     */
    public function reverseTransform($viewData)
    {
        if (is_array($viewData)) {
            $normData = [];

            /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData $productParameterValuesLocalizedData */
            foreach ($viewData as $productParameterValuesLocalizedData) {
                foreach ($productParameterValuesLocalizedData->valueTextsByLocale as $locale => $valueText) {
                    if ($valueText !== null) {
                        $productParameterValueData = $this->productParameterValueDataFactory->create();
                        $productParameterValueData->parameter = $productParameterValuesLocalizedData->parameter;
                        $parameterValueData = $this->parameterValueDataFactory->create();
                        $parameterValueData->text = $valueText;
                        $parameterValueData->locale = $locale;
                        $productParameterValueData->parameterValueData = $parameterValueData;

                        $normData[] = $productParameterValueData;
                    }
                }
            }

            return $normData;
        }

        throw new TransformationFailedException('Invalid value');
    }
}
