<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;

class VarDumperExtension extends Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
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
