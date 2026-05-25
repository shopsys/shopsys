<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

class SomeService implements ServiceSubscriberInterface
{
    public function process(ContainerBuilder $container): void
    {
    }

    public static function getSubscribedServices(): array
    {
        return [];
    }
}
