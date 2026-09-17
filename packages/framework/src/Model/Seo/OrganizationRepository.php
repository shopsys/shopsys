<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class OrganizationRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function findByDomainId(int $domainId): ?Organization
    {
        return $this->getOrganizationRepository()->findOneBy(['domainId' => $domainId]);
    }

    protected function getOrganizationRepository(): EntityRepository
    {
        return $this->em->getRepository(Organization::class);
    }
}
