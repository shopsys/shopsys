<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

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
    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', $this->renderIcon(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $iconName
     * @param array $attributes
     * @param string|null $iconType
     * @return string
     */
    public function renderIcon(string $iconName = '', array $attributes = [], ?string $iconType = 'svg-font'): string
    {
        $attributes['title'] = $attributes['title'] ?? null;
        $attributes['class'] = $attributes['class'] ?? null;
        $attributes['data'] = $attributes['data'] ?? [];

        return $this->twigEnvironment->render(
            '@ShopsysFramework/Components/Icon/icon.html.twig',
            [
                'name' => $iconName,
                'attr' => $attributes,
                'type' => $iconType,
            ],
        );
    }
}
