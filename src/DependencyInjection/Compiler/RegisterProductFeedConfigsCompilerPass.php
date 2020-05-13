<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterProductFeedConfigsCompilerPass as BaseRegisterProductFeedConfigsCompilerPass;
use Shopsys\FrameworkBundle\Model\Feed\FeedRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterProductFeedConfigsCompilerPass extends BaseRegisterProductFeedConfigsCompilerPass
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $feedRegistryDefinition = $container->findDefinition(FeedRegistry::class);

        $taggedServiceIds = $container->findTaggedServiceIds('sconto.product_feed');
        foreach ($taggedServiceIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $type = $tag['type'] ?? null;
                $feedRegistryDefinition->addMethodCall('registerFeed', [new Reference($serviceId), $type]);
            }
        }
    }
}
