<?php

declare(strict_types=1);


namespace App\Model\Product\Filter;

use App\Model\Product\Brand\CachedBrand;
use App\Model\Product\Flag\CachedFlag;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig as BaseProductFilterConfig;

class CachedProductFilterConfig extends BaseProductFilterConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     */
    public function __construct(BaseProductFilterConfig $productFilterConfig)
    {
        $this->setParameterChoices($productFilterConfig->getParameterChoices());
        /** @var \App\Model\Product\Flag\Flag[] $flagChoices */
        $flagChoices = $productFilterConfig->getFlagChoices();
        $this->setFlagChoices($flagChoices);

        /** @var \App\Model\Product\Brand\Brand[] $brandChoices */
        $brandChoices = $productFilterConfig->getBrandChoices();
        $this->setBrandChoices($brandChoices);
        
        $this->priceRange = $productFilterConfig->getPriceRange();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterChoices
     */
    private function setParameterChoices(array $parameterChoices): void
    {
        $cachedParameterFilterChoices = [];
        foreach ($parameterChoices as $parameterFilterChoice) {
            /** @var \App\Model\Product\Parameter\Parameter $parameter */
            $parameter = $parameterFilterChoice->getParameter();

            /** @var \App\Model\Product\Parameter\ParameterValue[] $values */
            $values = $parameterFilterChoice->getValues();

            $cachedParameterFilterChoice = new CachedParameterFilterChoice($parameter, $values);
            $cachedParameterFilterChoices[] = $cachedParameterFilterChoice;
        }
        $this->parameterChoices = $cachedParameterFilterChoices;
    }

    /**
     * @param \App\Model\Product\Brand\Brand[] $brandChoices
     */
    private function setBrandChoices(array $brandChoices): void
    {
        $cachedBrandChoices = [];
        foreach ($brandChoices as $brand) {
            $cachedBrandChoices[] = new CachedBrand($brand);
        }
        $this->brandChoices = $cachedBrandChoices;
    }

    /**
     * @param \App\Model\Product\Flag\Flag[] $flagChoices
     */
    private function setFlagChoices(array $flagChoices): void
    {
        $cachedFlagChoices = [];
        foreach ($flagChoices as $flag) {
            $cachedFlagChoices[] = new CachedFlag($flag);
        }
        $this->flagChoices = $cachedFlagChoices;
    }
}
