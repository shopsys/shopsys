<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;

class ModuleFacade
{
    protected const string ENABLED_MODULE_NAMES_CACHE_NAMESPACE = 'enabledModuleNames';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EnabledModuleRepository $enabledModuleRepository,
        protected readonly EnabledModuleFactory $enabledModuleFactory,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function isEnabled(string $moduleName): bool
    {
        $this->enabledModuleRepository->checkModuleIsSupported($moduleName);

        return in_array($moduleName, $this->getEnabledModuleNames(), true);
    }

    /**
     * Names of all enabled modules are loaded by a single query, because a lookup of a module that is not enabled
     * would always run its own query (there is no entity to be found in the identity map).
     *
     * @return string[]
     */
    protected function getEnabledModuleNames(): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::ENABLED_MODULE_NAMES_CACHE_NAMESPACE,
            fn (): array => $this->enabledModuleRepository->getAllNames(),
            'all',
        );
    }

    public function setEnabled(string $moduleName, bool $isEnabled): void
    {
        $enabledModule = $this->enabledModuleRepository->findByName($moduleName);

        if ($enabledModule === null && $isEnabled) {
            $enabledModule = $this->enabledModuleFactory->create($moduleName);
            $this->em->persist($enabledModule);
        } elseif ($enabledModule !== null && !$isEnabled) {
            $this->em->remove($enabledModule);
        }

        $this->em->flush();

        $this->inMemoryCache->deleteAllItemsInNamespace(static::ENABLED_MODULE_NAMES_CACHE_NAMESPACE);
    }
}
