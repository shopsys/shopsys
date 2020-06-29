<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\AkeneoHelper;
use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataAdministratorNonCriticalException;
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
     * @return array[]
     */
    public function getAllParameterOptionLocalizedLabels(string $akeneoAttributeCode): array
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
     * @param string $akeneoAttributeCode
     * @param mixed $parameterValue
     * @return array
     */
    public function getParameterOptionLocalizedLabels(string $akeneoAttributeCode, $parameterValue): array
    {
        $options = $this->getAllParameterOptionLocalizedLabels($akeneoAttributeCode);

        if (array_key_exists($parameterValue, $options) === false) {
            throw TransferInvalidDataAdministratorNonCriticalException::createWithViolation(
                sprintf(
                    'Parameter value %s for attribute code %s does not exist',
                    $parameterValue,
                    $akeneoAttributeCode
                ),
                ''
            );
        }

        return $options[$parameterValue];
    }
}
