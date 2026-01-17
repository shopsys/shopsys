<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle;

use Override;
use Shopsys\AdministrationBundle\DependencyInjection\Compiler\InitializeControllersCompilerPass;
use Shopsys\AdministrationBundle\DependencyInjection\Compiler\LoadControllersExtensionCompilerPass;
use Shopsys\AdministrationBundle\DependencyInjection\Compiler\RegisterControllerExtensionsCompilerPass;
use Shopsys\AdministrationBundle\DependencyInjection\ShopsysAdministrationExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ShopsysAdministrationBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ShopsysAdministrationExtension();
    }

    #[Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new InitializeControllersCompilerPass());
        $container->addCompilerPass(new RegisterControllerExtensionsCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 150);
        $container->addCompilerPass(new LoadControllersExtensionCompilerPass());
    }
}
