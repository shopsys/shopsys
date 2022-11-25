<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VarDumperExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'd',
                [$this, 'd']
            ),
        ];
    }

    /**
     * @param mixed $var
     */
    public function d(mixed $var): void
    {
        d($var);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'var_dumper_extension';
    }
}
