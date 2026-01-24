<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NoVarDumperExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('d', function (): void {
            }),
            new TwigFunction('dump', function (): void {
            }),
        ];
    }

    public function getName(): string
    {
        return 'no_var_dumper_extension';
    }
}
