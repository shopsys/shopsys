<?php

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\EntityManagerInterface;

class EnabledModuleRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\ModuleList
     */
    protected $moduleList;

    public function __construct(
        EntityManagerInterface $em,
        ModuleList $moduleList
    ) {
        $this->em = $em;
        $this->moduleList = $moduleList;
    }

    protected function getEnabledModuleRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(EnabledModule::class);
    }

    /**
     * @param string $moduleName
     */
    public function findByName($moduleName): ?\Shopsys\FrameworkBundle\Model\Module\EnabledModule
    {
        if (!in_array($moduleName, $this->moduleList->getNames(), true)) {
            throw new \Shopsys\FrameworkBundle\Model\Module\Exception\UnsupportedModuleException($moduleName);
        }

        return $this->getEnabledModuleRepository()->find($moduleName);
    }
}
