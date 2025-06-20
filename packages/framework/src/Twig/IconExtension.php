<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Symfony\UX\Icons\Twig\UXIconRuntime;
use Twig\DeprecatedCallableInfo;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IconExtension extends AbstractExtension
{
    /**
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        protected readonly Environment $twigEnvironment,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'icon',
                [UXIconRuntime::class, 'renderIcon'],
                [
                    'is_safe' => ['html'],
                    'deprecation_info' => new DeprecatedCallableInfo(
                        'shopsys/framework',
                        '17.0.0',
                        'ux_icon',
                        'symfony/ux-icons',
                    ),
                ],
            ),
            new TwigFunction('info_icon', $this->getInfoIcon(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $message
     * @param string $additionalClasses
     * @return string
     */
    protected function getInfoIcon(
        string $message,
        string $additionalClasses = '',
    ): string {
        return $this->twigEnvironment->render('@ShopsysAdministration/partial/info_icon.html.twig', [
            'message' => $message,
            'additionalClasses' => $additionalClasses,
        ]);
    }
}
