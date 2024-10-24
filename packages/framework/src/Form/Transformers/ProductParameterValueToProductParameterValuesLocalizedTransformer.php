<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductParameterValueToProductParameterValuesLocalizedTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface $parameterValueDataFactory
     */
    public function __construct(
        protected readonly ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
        protected readonly ParameterValueDataFactoryInterface $parameterValueDataFactory,
    ) {
    }

    /**
     * @param mixed $value
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData[]|null
     */
    public function transform($value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException('Invalid value');
        }

        $normData = [];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData $productParameterValueData */
        foreach ($value as $productParameterValueData) {
            $parameterId = $productParameterValueData->parameter->getId();
            $locale = $productParameterValueData->parameterValueData->locale;

            if (!array_key_exists($parameterId, $normData)) {
                $normData[$parameterId] = new ProductParameterValuesLocalizedData();
                $normData[$parameterId]->parameter = $productParameterValueData->parameter;
                $normData[$parameterId]->valueTextsByLocale = [];
            }

            $normData[$parameterId]->valueTextsByLocale[$locale] = $productParameterValueData->parameterValueData->text;

            if ($productParameterValueData->parameter->isSlider()) {
                $normData[$parameterId]->numericValue = $productParameterValueData->parameterValueData->text;
            }
        }

        return array_values($normData);
    }

    /**
     * @param mixed $value
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[]
     */
    public function reverseTransform($value): array
    {
        if (is_array($value)) {
            $modelData = [];

            /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData $productParameterValuesLocalizedData */
            foreach ($value as $productParameterValuesLocalizedData) {
                foreach ($productParameterValuesLocalizedData->valueTextsByLocale as $locale => $valueText) {
                    if ($valueText !== null) {
                        $productParameterValueData = $this->productParameterValueDataFactory->create();
                        $productParameterValueData->parameter = $productParameterValuesLocalizedData->parameter;
                        $parameterValueData = $this->parameterValueDataFactory->create();
                        $parameterValueData->text = $valueText;

                        if ($productParameterValuesLocalizedData->parameter->isSlider()) {
                            $parameterValueData->numericValue = $valueText;
                        }

                        $parameterValueData->locale = $locale;
                        $productParameterValueData->parameterValueData = $parameterValueData;

                        $modelData[] = $productParameterValueData;
                    }
                }
            }

            return $modelData;
        }

        throw new TransformationFailedException('Invalid value');
    }
}
