<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterProductFeedConfigsCompilerPass implements CompilerPassInterface
{
    #[Override]
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
                        $tag['cron'],
                        isset($tag['domain_ids']) ? $this->splitDomainIdsFromString($tag['domain_ids']) : [],
                        $tag['currencies'] ?? null,
                    ],
                );
            }
        }
    }

    /**
     * @return int[]
     */
    private function splitDomainIdsFromString(string $domainIds): array
    {
        return array_map('intval', explode(',', $domainIds));
    }
}
