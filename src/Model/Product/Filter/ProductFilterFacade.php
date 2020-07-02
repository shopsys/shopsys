<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Component\Router\CategorySeoMix\CategorySeoMixUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductFilterFacade
{
    public const BASE_PRODUCT_FILTER_PARAM = 'product_filter_form';

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \App\Component\Router\CategorySeoMix\CategorySeoMixUrlGenerator
     */
    private $categorySeoMixUrlGenerator;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    private $generator;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \App\Component\Router\CategorySeoMix\CategorySeoMixUrlGenerator $categorySeoMixUrlGenerator
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $generator
     */
    public function __construct(
        RequestStack $requestStack,
        CategorySeoMixUrlGenerator $categorySeoMixUrlGenerator,
        UrlGeneratorInterface $generator
    ) {
        $this->requestStack = $requestStack;
        $this->categorySeoMixUrlGenerator = $categorySeoMixUrlGenerator;
        $this->generator = $generator;
    }

    /**
     * @param array $productFilterSetup
     * @return string
     */
    public function getUrlByProductFilterSetup(array $productFilterSetup): string
    {
        $routeName = $this->requestStack->getCurrentRequest()->attributes->get('_route');
        $routeParams = $this->requestStack->getCurrentRequest()->attributes->get('_route_params');
        $fullProductFilterSetup[self::BASE_PRODUCT_FILTER_PARAM] = $productFilterSetup;

        if ($routeName === 'front_product_list') {
            return $this->categorySeoMixUrlGenerator->generateUrlWithFallbackToProductList($routeParams['id'], $fullProductFilterSetup, UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $baseUrl = $this->generator->generate($routeName, $routeParams, UrlGeneratorInterface::ABSOLUTE_URL);
        $productFilterUrl = http_build_query($fullProductFilterSetup);
        if ($productFilterUrl === '') {
            return $baseUrl;
        }

        return $baseUrl . '?' . $productFilterUrl;
    }

    /**
     * @param array $productFilterSetup
     * @param int|string $type
     * @param mixed $value
     */
    public function addValueOfCollectionToProductFilterSetup(array &$productFilterSetup, $type, $value): void
    {
        $productFilterSetup[$type][$value] = $value;
    }

    /**
     * @param array $productFilterSetup
     * @param int|string $type
     * @param mixed $value
     */
    public function unsetValueOfCollectionToProductFilterSetup(array &$productFilterSetup, $type, $value): void
    {
        unset($productFilterSetup[$type][$value]);
        if (count($productFilterSetup[$type]) === 0) {
            unset($productFilterSetup[$type]);
        }
    }

    /**
     * @param array $productFilterSetup
     * @param string $type
     * @param int $value
     */
    public function setYesNoToProductFilterSetup(array &$productFilterSetup, string $type, int $value): void
    {
        if ($value === 0) {
            unset($productFilterSetup[$type]);
        } else {
            $productFilterSetup[$type] = $value;
        }
    }

    /**
     * @param array $productFilterSetup
     * @param int|string $firstCollectionType
     * @param int|string $secondCollectionType
     * @param mixed $value
     */
    public function addValueOfCollectionOfCollectionToProductFilterSetup(array &$productFilterSetup, $firstCollectionType, $secondCollectionType, $value): void
    {
        $productFilterSetup[$firstCollectionType][$secondCollectionType][$value] = $value;
    }

    /**
     * @param array $productFilterSetup
     * @param int|string $firstCollectionType
     * @param int|string $secondCollectionType
     * @param mixed $value
     */
    public function unsetValueOfCollectionOfCollectionToProductFilterSetup(array &$productFilterSetup, $firstCollectionType, $secondCollectionType, $value): void
    {
        unset($productFilterSetup[$firstCollectionType][$secondCollectionType][$value]);
        if (count($productFilterSetup[$firstCollectionType][$secondCollectionType]) === 0) {
            unset($productFilterSetup[$firstCollectionType][$secondCollectionType]);
        }
        if (count($productFilterSetup[$firstCollectionType]) === 0) {
            unset($productFilterSetup[$firstCollectionType]);
        }
    }

    /**
     * @param array $productFilterFormRequestData
     * @return array
     */
    public function getProductFilterSetupByProductFilterFormRequestData(array $productFilterFormRequestData): array
    {
        return $this->processProductFilterFormRequestData($productFilterFormRequestData);
    }

    /**
     * @param array $productFilterFormRequestData
     * @return array
     */
    private function processProductFilterFormRequestData(array $productFilterFormRequestData): array
    {
        $setup = [];
        foreach ($productFilterFormRequestData as $key => $value) {
            if (is_array($value)) {
                $setup[$key] = $this->processProductFilterFormRequestData($value);
            } else {
                if (is_numeric($key)) {
                    $setup[$value] = $value;
                } else {
                    $setup[$key] = $value;
                }
            }
        }

        return $setup;
    }
}
