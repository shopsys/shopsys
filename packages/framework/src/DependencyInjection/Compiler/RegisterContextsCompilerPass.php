<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AbstractContext;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterContextsCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(AbstractContext::class)
            ->addTag('shopsys.context');
    }
}
