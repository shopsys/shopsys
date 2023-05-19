<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Module\Exception\UnsupportedModuleException;

class EnabledModuleRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleList $moduleList
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ModuleList $moduleList,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getEnabledModuleRepository()
    {
        return $this->em->getRepository(EnabledModule::class);
    }

    /**
     * @param string $moduleName
     * @return \Shopsys\FrameworkBundle\Model\Module\EnabledModule|null
     */
    public function findByName($moduleName)
    {
        if (!in_array($moduleName, $this->moduleList->getNames(), true)) {
            throw new UnsupportedModuleException($moduleName);
        }

        return $this->getEnabledModuleRepository()->find($moduleName);
    }
}
