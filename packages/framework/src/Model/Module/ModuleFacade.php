<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\EntityManagerInterface;

class ModuleFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Module\EnabledModuleRepository $enabledModuleRepository
     * @param \Shopsys\FrameworkBundle\Model\Module\EnabledModuleFactoryInterface $enabledModuleFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EnabledModuleRepository $enabledModuleRepository,
        protected readonly EnabledModuleFactoryInterface $enabledModuleFactory,
    ) {
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
