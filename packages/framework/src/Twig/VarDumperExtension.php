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
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'd',
                $this->d(...),
            ),
        ];
    }

    /**
     * @param mixed $var
     */
    public function d($var): void
    {
        d($var);
    }

    public function getName()
    {
        return 'var_dumper_extension';
    }
}
