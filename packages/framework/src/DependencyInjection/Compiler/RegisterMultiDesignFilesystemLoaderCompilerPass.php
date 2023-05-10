<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Shopsys\FrameworkBundle\Component\Domain\Multidomain\Twig\FilesystemLoader;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterMultiDesignFilesystemLoaderCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.native_filesystem');
        $twigFilesystemLoaderDefinition->setClass(FilesystemLoader::class);
        $twigFilesystemLoaderDefinition->setAutowired(true);

        $environment = $container->getParameter('kernel.environment');

        if ($environment === EnvironmentType::TEST) {
            $twigFilesystemLoaderDefinition->setPublic(true);
        }
    }
}
