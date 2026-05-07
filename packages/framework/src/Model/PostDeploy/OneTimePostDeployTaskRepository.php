<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PostDeploy;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class OneTimePostDeployTaskRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return string[]
     */
    public function getAllNames(): array
    {
        return $this->getRepository()
            ->createQueryBuilder('r')
            ->select('r.name')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\FrameworkBundle\Model\PostDeploy\OneTimePostDeployTaskRecord>
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(OneTimePostDeployTaskRecord::class);
    }
}
