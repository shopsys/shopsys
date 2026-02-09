<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Module\Exception\UnsupportedModuleException;

class EnabledModuleRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ModuleList $moduleList,
    ) {
    }

    protected function getEnabledModuleRepository(): EntityRepository
    {
        return $this->em->getRepository(EnabledModule::class);
    }

    public function findByName(string $moduleName): ?EnabledModule
    {
        if (!in_array($moduleName, $this->moduleList->getNames(), true)) {
            throw new UnsupportedModuleException($moduleName);
        }

        return $this->getEnabledModuleRepository()->find($moduleName);
    }
}
