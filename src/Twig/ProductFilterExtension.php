<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Filter\ProductFilterFacade;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Flag\FlagFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductFilterExtension extends AbstractExtension
{
    /**
     * @var \App\Model\Product\Filter\ProductFilterFacade
     */
    private $productFilterFacade;

    /**
     * @var \App\Model\Product\Flag\FlagFacade
     */
    private $flagFacade;

    /**
     * @param \App\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     */
    public function __construct(ProductFilterFacade $productFilterFacade, FlagFacade $flagFacade)
    {
        $this->productFilterFacade = $productFilterFacade;
        $this->flagFacade = $flagFacade;
    }

    public function getFunctions()
    {
        $functions = parent::getFunctions();
        $functions[] = new TwigFunction('getUrlByParameterValue', [$this, 'getUrlByParameterValue']);
        $functions[] = new TwigFunction('getUrlByFlagValue', [$this, 'getUrlByFlagValue']);
        $functions[] = new TwigFunction('getUrlByBrandValue', [$this, 'getUrlByBrandValue']);
        $functions[] = new TwigFunction('getUrlByAvailabilityValue', [$this, 'getUrlByAvailabilityValue']);
        $functions[] = new TwigFunction('getFlagIconClass', [$this, 'getFlagIconClass']);

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

    /**
     * @param int $flagId
     * @return string
     */
    public function getFlagIconClass(int $flagId): string
    {
        $flag = $this->flagFacade->getById($flagId);
        switch ($flag->getAkeneoCode()) {
            case Flag::AKENEO_CODE_NEW:
                $class = 'flag-icon_new';
                break;
            case Flag::AKENEO_CODE_SCONTO:
                $class = 'flag-icon_sconto';
                break;
            case Flag::AKENEO_CODE_ACTION:
                $class = 'flag-icon_action';
                break;
            case Flag::AKENEO_CODE_HIT:
                $class = 'flag-icon_hit';
                break;
            case Flag::AKENEO_CODE_SALE:
                $class = 'flag-icon_sale';
                break;
            case Flag::AKENEO_CODE_MADE_IN_CZ:
                $class = 'flag-icon_made-in-cz';
                break;
            case Flag::AKENEO_CODE_MADE_IN_DE:
                $class = 'flag-icon_made-in-de';
                break;
            case Flag::AKENEO_CODE_MADE_IN_SK:
                $class = 'flag-icon_made-in-sk';
                break;
            default:
                $class = '';
                break;
        }

        return $class;
    }
}
