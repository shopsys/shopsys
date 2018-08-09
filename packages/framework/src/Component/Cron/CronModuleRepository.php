<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

use Doctrine\ORM\EntityManagerInterface;

class CronModuleRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronModuleFactoryInterface
     */
    protected $cronModuleFactory;

    public function __construct(EntityManagerInterface $em, CronModuleFactoryInterface $cronModuleFactory)
    {
        $this->em = $em;
        $this->cronModuleFactory = $cronModuleFactory;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCronModuleRepository()
    {
        return $this->em->getRepository(CronModule::class);
    }

    public function getCronModuleByServiceId($serviceId)
    {
        $cronModule = $this->getCronModuleRepository()->find($serviceId);
        if ($cronModule === null) {
            $cronModule = $this->cronModuleFactory->create($serviceId);
            $this->em->persist($cronModule);
            $this->em->flush($cronModule);
        }

        return $cronModule;
    }

    /**
     * @return string[]
     */
    public function getAllScheduledCronModuleServiceIds()
    {
        $query = $this->em->createQuery('SELECT cm.serviceId FROM ' . CronModule::class . ' cm WHERE cm.scheduled = TRUE');

        return array_map('array_pop', $query->getScalarResult());
    }
}
