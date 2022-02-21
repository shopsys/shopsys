<?php

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\EntityManagerInterface;

class EnabledModuleRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\ModuleList
     */
    protected $moduleList;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleList $moduleList
     */
    public function __construct(
        EntityManagerInterface $em,
        ModuleList $moduleList
    ) {
        $this->em = $em;
        $this->moduleList = $moduleList;
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
            throw new \Shopsys\FrameworkBundle\Model\Module\Exception\UnsupportedModuleException($moduleName);
        }

        return $this->getEnabledModuleRepository()->find($moduleName);
    }
}
