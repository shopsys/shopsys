<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\CategorySeo\ChoseCategorySeoMixCombination;
use App\Model\Product\Parameter\ParameterFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategorySeoExtension extends AbstractExtension
{
    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(
        ParameterFacade $parameterFacade,
        RouterInterface $router
    ) {
        $this->parameterFacade = $parameterFacade;
        $this->router = $router;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getReadyCategoryMixCombinationParametersPairsIterator', [$this, 'getReadyCategoryMixCombinationParametersPairsIterator']),
            new TwigFunction('getAbsoluteUrlOfReadyCategorySeoMix', [$this, 'getAbsoluteUrlOfReadyCategorySeoMix']),
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

    public function getAbsoluteUrlOfReadyCategorySeoMix(int $readyCategorySeoMixId)
    {
        return $this->router->generate('front_category_seo', [
            'id' => $readyCategorySeoMixId,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
