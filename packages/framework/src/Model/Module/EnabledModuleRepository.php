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
        $this->checkModuleIsSupported($moduleName);

        return $this->getEnabledModuleRepository()->find($moduleName);
    }

    /**
     * @return string[]
     */
    public function getAllNames(): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('em.name')
            ->from(EnabledModule::class, 'em');

        return array_column($queryBuilder->getQuery()->getArrayResult(), 'name');
    }

    public function checkModuleIsSupported(string $moduleName): void
    {
        if (!in_array($moduleName, $this->moduleList->getNames(), true)) {
            throw new UnsupportedModuleException($moduleName);
        }
    }
}
