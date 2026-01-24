<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VarDumperExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'd',
                $this->d(...),
            ),
        ];
    }

    public function d(mixed $var): void
    {
        d($var);
    }

    public function getName(): string
    {
        return 'var_dumper_extension';
    }
}
