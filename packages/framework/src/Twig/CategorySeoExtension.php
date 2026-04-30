<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Generator;
use Override;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategorySeoExtension extends AbstractExtension
{
    public function __construct(
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getReadyCategoryMixCombinationParametersPairsIterator', $this->getReadyCategoryMixCombinationParametersPairsIterator(...)),
            new TwigFunction('getAbsoluteUrlOfReadyCategorySeoMix', $this->getAbsoluteUrlOfReadyCategorySeoMix(...)),
        ];
    }

    public function getReadyCategoryMixCombinationParametersPairsIterator(int $readyCategorySeoMixId): Generator
    {
        $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getById($readyCategorySeoMixId);

        foreach ($readyCategorySeoMix->getReadyCategorySeoMixParameterParameterValues() as $readyCategorySeoMixParameterParameterValue) {
            yield $readyCategorySeoMixParameterParameterValue->getParameter()->getName() . ': ' . $readyCategorySeoMixParameterParameterValue->getParameterValue()->getText();
        }
    }

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
