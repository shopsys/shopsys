<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NoVarDumperExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('d', function () {}),
            new TwigFunction('dump', function () {}),
        ];
    }

    public function getName()
    {
        return 'no_var_dumper_extension';
    }
}
