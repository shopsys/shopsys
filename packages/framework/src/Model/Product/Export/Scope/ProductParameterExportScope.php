<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductParameterExportScope extends AbstractProductExportScope
{
    public function __construct(
        private readonly ParameterRepository $parameterRepository,
    )
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $object
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    public function map(object $object, string $locale, int $domainId): array
    {
        return [
            'parameters' => $this->extractParameters($locale, $object),
        ];
    }

    /**
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractParameters(string $locale, Product $product): array
    {
        $parameters = [];
        $productParameterValues = $this->parameterRepository->getProductParameterValuesByProductSortedByName(
            $product,
            $locale,
        );

        foreach ($productParameterValues as $productParameterValue) {
            $parameter = $productParameterValue->getParameter();
            $parameterValue = $productParameterValue->getValue();

            if ($parameter->getName($locale) === null || $parameterValue->getLocale() !== $locale) {
                continue;
            }

            $parameters[] = [
                'parameter_id' => $parameter->getId(),
                'parameter_uuid' => $parameter->getUuid(),
                'parameter_name' => $parameter->getName($locale),
                'parameter_value_id' => $parameterValue->getId(),
                'parameter_value_uuid' => $parameterValue->getUuid(),
                'parameter_value_text' => $parameterValue->getText(),
            ];
        }

        return $parameters;
    }
}
