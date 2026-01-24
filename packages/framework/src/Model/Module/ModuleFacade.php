<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\EntityManagerInterface;

class ModuleFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EnabledModuleRepository $enabledModuleRepository,
        protected readonly EnabledModuleFactory $enabledModuleFactory,
    ) {
    }

    public function isEnabled(string $moduleName): bool
    {
        $enabledModule = $this->enabledModuleRepository->findByName($moduleName);

        return $enabledModule !== null;
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
    }
}
