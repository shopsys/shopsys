<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use Override;
use Shopsys\AdministrationBundle\Twig\Runtime\DomainIconRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DomainIconExtension extends AbstractExtension
{
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('domain_icon', [DomainIconRuntime::class, 'renderDomainIcon'], ['is_safe' => ['html']]),
        ];
    }
}
