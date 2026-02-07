<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Parameter;

use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;

class ParameterWithValuesFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     */
    public function __construct(
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
    ) {
    }

    /**
     * @param array $productData
     * @return array
     */
    public function createParametersArrayFromProductArray(array $productData): array
    {
        $parametersWithValues = [];

        foreach ($productData['parameters'] as $parameterArray) {
            $parameterUuid = $parameterArray['parameter_uuid'];

            if (!array_key_exists($parameterUuid, $parametersWithValues)) {
                $parametersWithValues[$parameterUuid] = $this->mapParameterArray($parameterArray);
            }

            $parametersWithValues[$parameterUuid]['values'][] = [
                'uuid' => $parameterArray['parameter_value_uuid'],
                'text' => $parameterArray['parameter_value_text'],
                'rgbHex' => $parameterArray['parameter_value_rgbHex'],
                'colorIcon' => $this->mapColorIcon($parameterArray),
            ];
        }

        return $parametersWithValues;
    }

    /**
     * @param array $product
     * @return array
     */
    protected function mapParameterArray(array $product): array
    {
        return [
            'uuid' => $product['parameter_uuid'],
            'name' => $product['parameter_name'],
            'group' => $product['parameter_group'],
            'unit' => $product['parameter_unit'] ? ['name' => $product['parameter_unit']] : null,
            'values' => [],
        ];
    }

    /**
     * @param array $parameterArray
     * @return array|null
     */
    protected function mapColorIcon(array $parameterArray): ?array
    {
        $anchorText = $parameterArray['parameter_value_icon_anchor_text'];
        $url = $parameterArray['parameter_value_icon_url'];

        if ($anchorText === null || $url === null) {
            return null;
        }

        return [
            'anchorText' => $anchorText,
            'url' => $url,
        ];
    }
}
