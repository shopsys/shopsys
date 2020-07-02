<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Filter\ProductFilterFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductFilterExtension extends AbstractExtension
{
    /**
     * @var \App\Model\Product\Filter\ProductFilterFacade
     */
    private $productFilterFacade;

    /**
     * @param \App\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     */
    public function __construct(ProductFilterFacade $productFilterFacade)
    {
        $this->productFilterFacade = $productFilterFacade;
    }

    public function getFunctions()
    {
        $functions = parent::getFunctions();
        $functions[] = new TwigFunction('getUrlByParameterValue', [$this, 'getUrlByParameterValue']);
        $functions[] = new TwigFunction('getUrlByFlagValue', [$this, 'getUrlByFlagValue']);
        $functions[] = new TwigFunction('getUrlByBrandValue', [$this, 'getUrlByBrandValue']);
        $functions[] = new TwigFunction('getUrlByAvailabilityValue', [$this, 'getUrlByAvailabilityValue']);

        return $functions;
    }

    /**
     * @param array $productFilterSetup
     * @param int $parameterId
     * @param int $parameterValueId
     * @param bool $checked
     * @return string
     */
    public function getUrlByParameterValue(array $productFilterSetup, int $parameterId, int $parameterValueId, bool $checked): string
    {
        $tmpProductFilterSetup = $productFilterSetup;
        if ($checked) {
            $this->productFilterFacade->unsetValueOfCollectionOfCollectionToProductFilterSetup($tmpProductFilterSetup, 'parameters', $parameterId, $parameterValueId);
        } else {
            $this->productFilterFacade->addValueOfCollectionOfCollectionToProductFilterSetup($tmpProductFilterSetup, 'parameters', $parameterId, $parameterValueId);
        }

        return $this->productFilterFacade->getUrlByProductFilterSetup($tmpProductFilterSetup);
    }

    /**
     * @param array $productFilterSetup
     * @param int $flagId
     * @param bool $checked
     * @return string
     */
    public function getUrlByFlagValue(array $productFilterSetup, int $flagId, bool $checked): string
    {
        $tmpProductFilterSetup = $productFilterSetup;
        if ($checked) {
            $this->productFilterFacade->unsetValueOfCollectionToProductFilterSetup($tmpProductFilterSetup, 'flags', $flagId);
        } else {
            $this->productFilterFacade->addValueOfCollectionToProductFilterSetup($tmpProductFilterSetup, 'flags', $flagId);
        }

        return $this->productFilterFacade->getUrlByProductFilterSetup($tmpProductFilterSetup);
    }

    /**
     * @param array $productFilterSetup
     * @param int $brandId
     * @param bool $checked
     * @return string
     */
    public function getUrlByBrandValue(array $productFilterSetup, int $brandId, bool $checked): string
    {
        $tmpProductFilterSetup = $productFilterSetup;
        if ($checked) {
            $this->productFilterFacade->unsetValueOfCollectionToProductFilterSetup($tmpProductFilterSetup, 'brands', $brandId);
        } else {
            $this->productFilterFacade->addValueOfCollectionToProductFilterSetup($tmpProductFilterSetup, 'brands', $brandId);
        }

        return $this->productFilterFacade->getUrlByProductFilterSetup($tmpProductFilterSetup);
    }

    /**
     * @param array $productFilterSetup
     * @param bool $checked
     * @return string
     */
    public function getUrlByAvailabilityValue(array $productFilterSetup, bool $checked): string
    {
        $tmpProductFilterSetup = $productFilterSetup;

        $this->productFilterFacade->setYesNoToProductFilterSetup($tmpProductFilterSetup, 'inStock', $checked ? 0 : 1);

        return $this->productFilterFacade->getUrlByProductFilterSetup($tmpProductFilterSetup);
    }
}
