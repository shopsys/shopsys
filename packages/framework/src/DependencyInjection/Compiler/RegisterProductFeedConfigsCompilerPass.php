<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Shopsys\FrameworkBundle\Model\Feed\FeedRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterProductFeedConfigsCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $feedRegistryDefinition = $container->findDefinition(FeedRegistry::class);

        $taggedServiceIds = $container->findTaggedServiceIds('shopsys.feed');

        foreach ($taggedServiceIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $feedRegistryDefinition->addMethodCall(
                    'registerFeed',
                    [
                        new Reference($serviceId),
                        $tag['hours'],
                        $tag['minutes'],
                        isset($tag['domain_ids']) ? $this->splitDomainIdsFromString($tag['domain_ids']) : [],
                    ],
                );
            }
        }
    }

    /**
     * @param string $domainIds
     * @return int[]
     */
    protected function splitDomainIdsFromString(string $domainIds): array
    {
        return array_map('intval', explode(',', $domainIds));
    }
}
