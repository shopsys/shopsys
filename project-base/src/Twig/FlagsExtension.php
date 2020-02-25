<?php

declare(strict_types=1);

namespace App\Twig;

use Shopsys\ReadModelBundle\Flag\FlagsProvider;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlagsExtension extends AbstractExtension
{
    /**
     * @var \Twig\Environment
     */
    protected $twigEnvironment;

    /**
     * @var \Shopsys\ReadModelBundle\Flag\FlagsProvider
     */
    protected $flagsProvider;

    /**
     * @param \Shopsys\ReadModelBundle\Flag\FlagsProvider $flagsProvider
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        FlagsProvider $flagsProvider,
        Environment $twigEnvironment
    ) {
        $this->twigEnvironment = $twigEnvironment;
        $this->flagsProvider = $flagsProvider;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderFlagsByIds', [$this, 'renderFlagsByIds'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param int[] $flagIds
     * @param string $classAddition
     * @return string
     */
    public function renderFlagsByIds(array $flagIds, string $classAddition = ''): string
    {
        return $this->twigEnvironment->render(
            'Front/Inline/Product/productFlags.html.twig',
            [
                'flags' => $this->flagsProvider->getFlagsByIds($flagIds),
                'classAddition' => $classAddition,
            ]
        );
    }
}
