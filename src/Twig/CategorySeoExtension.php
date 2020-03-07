<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\CategorySeo\ChoseCategorySeoMixCombination;
use App\Model\Product\Parameter\ParameterFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategorySeoExtension extends AbstractExtension
{
    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        ParameterFacade $parameterFacade
    ) {
        $this->parameterFacade = $parameterFacade;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getReadyCategoryMixCombinationParametersPairsIterator', [$this, 'getReadyCategoryMixCombinationParametersPairsIterator']),
        ];
    }

    /**
     * @param string $choseCategorySeoMixCombinationJson
     * @return \Iterator
     */
    public function getReadyCategoryMixCombinationParametersPairsIterator(string $choseCategorySeoMixCombinationJson)
    {
        $choseCategorySeoMixCombination = ChoseCategorySeoMixCombination::createFromJson($choseCategorySeoMixCombinationJson);

        foreach ($choseCategorySeoMixCombination->getParameterValueIdsByParameterIds() as $parameterId => $parameterValueId) {
            yield $this->parameterFacade->getById($parameterId)->getName() . ': ' . $this->parameterFacade->getParameterValueById($parameterValueId)->getText();
        }
    }
}
