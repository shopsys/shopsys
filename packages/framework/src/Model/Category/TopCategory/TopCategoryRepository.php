<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\TopCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class TopCategoryRepository
{
    protected EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->em = $entityManager;
    }

    protected function getTopCategoryRepository(): EntityRepository
    {
        return $this->em->getRepository(TopCategory::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getTopCategoryRepository()->findBy(['domainId' => $domainId], ['position' => 'ASC']);
    }
}
