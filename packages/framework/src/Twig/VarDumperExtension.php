<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VarDumperExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
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
    public function d($var)
    {
        d($var);
    }

    public function getName()
    {
        return 'var_dumper_extension';
    }
}
