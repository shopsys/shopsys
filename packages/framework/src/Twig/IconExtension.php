<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IconExtension extends AbstractExtension
{
    /**
     * @param \Twig\Environment $twigEnvironment
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(
        protected readonly Environment $twigEnvironment,
        protected readonly ParameterBagInterface $parameterBag,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', $this->renderIcon(...), ['is_safe' => ['html']]),
            new TwigFunction('iconInfo', $this->renderInfoIcon(...), ['is_safe' => ['html']]),
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

    /**
     * @param string $title
     * @return string
     */
    public function renderInfoIcon(string $title): string
    {
        return $this->renderIcon(
            'info-circle-fill',
            [
                'class' => 'cursor cursor-help js-tooltip box-quick-search__icon in-icon in-icon--info',
                'data' => ['toggle' => 'tooltip', 'placement' => 'top'],
                'title'=> $title
            ]
        );
    }
}
