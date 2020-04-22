<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\CategorySeo\ChoseCategorySeoMixCombination;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use App\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategorySeoExtension extends AbstractExtension
{
    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    private $domainRouterFactory;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        ParameterFacade $parameterFacade,
        DomainRouterFactory $domainRouterFactory,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
    ) {
        $this->parameterFacade = $parameterFacade;
        $this->domainRouterFactory = $domainRouterFactory;
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
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

    /**
     * @param int $readyCategorySeoMixId
     * @return string
     */
    public function getAbsoluteUrlOfReadyCategorySeoMix(int $readyCategorySeoMixId): string
    {
        $readyCategorySeoMix = $this->readyCategorySeoMixFacade->findById($readyCategorySeoMixId);

        if ($readyCategorySeoMix === null) {
            return '#';
        }

        $readyCategorySeoMixDomainRouter = $this->domainRouterFactory->getRouter($readyCategorySeoMix->getDomainId());

        return $readyCategorySeoMixDomainRouter->generate('front_category_seo', [
            'id' => $readyCategorySeoMixId,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
