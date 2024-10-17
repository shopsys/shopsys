<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Generator;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\CategorySeo\ChoseCategorySeoMixCombination;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategorySeoExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        protected readonly ParameterFacade $parameterFacade,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getReadyCategoryMixCombinationParametersPairsIterator', $this->getReadyCategoryMixCombinationParametersPairsIterator(...)),
            new TwigFunction('getAbsoluteUrlOfReadyCategorySeoMix', $this->getAbsoluteUrlOfReadyCategorySeoMix(...)),
        ];
    }

    /**
     * @param string $choseCategorySeoMixCombinationJson
     * @return \Generator
     */
    public function getReadyCategoryMixCombinationParametersPairsIterator(
        string $choseCategorySeoMixCombinationJson,
    ): Generator {
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
