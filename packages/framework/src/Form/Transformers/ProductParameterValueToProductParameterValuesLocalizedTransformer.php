<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedDataFactory;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductParameterValueToProductParameterValuesLocalizedTransformer implements DataTransformerInterface
{
    public function __construct(
        protected readonly ProductParameterValueDataFactory $productParameterValueDataFactory,
        protected readonly ProductParameterValuesLocalizedDataFactory $productParameterValuesLocalizedDataFactory,
    ) {
    }

    /**
     * @param mixed $value
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData[]|null
     */
    #[Override]
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
                $normData[$parameterId] = $this->productParameterValuesLocalizedDataFactory->createFromProductParameterValueData($productParameterValueData);
            } else {
                $normData[$parameterId]->valueTextsByLocale[$locale] = $productParameterValueData->parameterValueData->text;
            }
        }

        return array_values($normData);
    }

    /**
     * @param mixed $value
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[]
     */
    #[Override]
    public function reverseTransform($value): array
    {
        if (is_array($value)) {
            $modelData = [];

            /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData $productParameterValuesLocalizedData */
            foreach ($value as $productParameterValuesLocalizedData) {
                foreach ($this->productParameterValueDataFactory->createMultipleFromProductParameterValuesLocalizedData($productParameterValuesLocalizedData) as $productParameterValueData) {
                    $modelData[] = $productParameterValueData;
                }
            }

            return $modelData;
        }

        throw new TransformationFailedException('Invalid value');
    }
}
