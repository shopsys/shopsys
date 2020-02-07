<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PublicServicesForFunctionalTestsCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getAliases() as $serviceId => $alias) {
            if ($this->isShopClass($serviceId)) {
                $alias->setPublic(true);
            }
        }
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if ($this->isShopClass($serviceId)) {
                $definition->setPublic(true);
            }
        }
    }

    /**
     * @param string $serviceId
     * @return bool
     */
    private function isShopClass(string $serviceId): bool
    {
        return strpos($serviceId, 'Shopsys\FrameworkBundle') !== false
            || strpos($serviceId, 'Shopsys\ReadModelBundle') !== false
            || strpos($serviceId, 'App') !== false;
    }
}
