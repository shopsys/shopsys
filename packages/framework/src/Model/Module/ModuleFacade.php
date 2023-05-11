<?php

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\EntityManagerInterface;

class ModuleFacade
{
    protected EntityManagerInterface $em;

    protected EnabledModuleRepository $enabledModuleRepository;

    protected EnabledModuleFactoryInterface $enabledModuleFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Module\EnabledModuleRepository $enabledModuleRepository
     * @param \Shopsys\FrameworkBundle\Model\Module\EnabledModuleFactoryInterface $enabledModuleFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        EnabledModuleRepository $enabledModuleRepository,
        EnabledModuleFactoryInterface $enabledModuleFactory
    ) {
        $this->em = $em;
        $this->enabledModuleRepository = $enabledModuleRepository;
        $this->enabledModuleFactory = $enabledModuleFactory;
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isEnabled($moduleName)
    {
        $enabledModule = $this->enabledModuleRepository->findByName($moduleName);

        return $enabledModule !== null;
    }

    /**
     * @param string $moduleName
     * @param bool $isEnabled
     */
    public function setEnabled($moduleName, $isEnabled)
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
