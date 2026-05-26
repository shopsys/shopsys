<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;

class FullyQualifiedCompilerPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
    }
}
