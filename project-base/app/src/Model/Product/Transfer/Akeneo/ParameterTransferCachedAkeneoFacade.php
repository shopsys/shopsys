<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\AkeneoHelper;
use App\Component\Akeneo\Transfer\Exception\TransferException;
use App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoFacade;

class ParameterTransferCachedAkeneoFacade
{
    /**
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoFacade
     */
    private $productParameterTransferAkeneoFacade;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoFacade $productParameterTransferAkeneoFacade
     */
    public function __construct(ProductParameterTransferAkeneoFacade $productParameterTransferAkeneoFacade)
    {
        $this->productParameterTransferAkeneoFacade = $productParameterTransferAkeneoFacade;
    }

    /**
     * @param string $akeneoAttributeCode
     * @return string[][]
     */
    private function getAllParameterOptionLocalizedLabels(string $akeneoAttributeCode): array
    {
        if (array_key_exists($akeneoAttributeCode, $this->cache)) {
            return $this->cache[$akeneoAttributeCode];
        }

        foreach ($this->productParameterTransferAkeneoFacade->getAttributeOptions($akeneoAttributeCode) as $option) {
            $this->cache[$akeneoAttributeCode][$option['code']] = AkeneoHelper::mapLocalizedLabels([], $option);
        }

        return $this->cache[$akeneoAttributeCode];
    }

    /**
     * @param string $parameterAkeneoCode
     * @param string $akeneoParameterValueCode
     * @return string[]|null[]
     */
    public function getParameterValueTextsIndexedByLocaleForParameterAndAkeneoValue(string $parameterAkeneoCode, string $akeneoParameterValueCode): array
    {
        $parameterValueTextsByAkeneoCodeAndLocale = $this->getAllParameterOptionLocalizedLabels($parameterAkeneoCode);

        if (array_key_exists($akeneoParameterValueCode, $parameterValueTextsByAkeneoCodeAndLocale) === false) {
            throw new TransferException(
                sprintf(
                    'Parameter value %s for attribute code %s does not exist',
                    $akeneoParameterValueCode,
                    $parameterAkeneoCode
                ),
                0
            );
        }

        return $parameterValueTextsByAkeneoCodeAndLocale[$akeneoParameterValueCode];
    }
}
