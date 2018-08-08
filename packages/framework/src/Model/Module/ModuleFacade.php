<?php

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\EntityManagerInterface;

class ModuleFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\EnabledModuleRepository
     */
    protected $enabledModuleRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\EnabledModuleFactoryInterface
     */
    protected $enabledModuleFactory;

    public function __construct(
        EntityManagerInterface $em,
        EnabledModuleRepository $enabledModuleRepository,
        EnabledModuleFactoryInterface $enabledModuleFactory
    ) {
        $this->em = $em;
        $this->enabledModuleRepository = $enabledModuleRepository;
        $this->enabledModuleFactory = $enabledModuleFactory;
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
