<?php

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface as SymfonyCompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AliasedCompilerPass implements SymfonyCompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
    }
}
