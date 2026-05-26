<?php

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExtendedCompilerPass extends BaseCompilerPass implements EventSubscriberInterface, CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
