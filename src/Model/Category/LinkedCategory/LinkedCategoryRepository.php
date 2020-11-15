<?php

declare(strict_types=1);

namespace App\Model\Category\LinkedCategory;

use App\Model\Category\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class LinkedCategoryRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getLinkedCategoryRepository(): EntityRepository
    {
        return $this->em->getRepository(LinkedCategory::class);
    }

    /**
     * @param \App\Model\Category\Category $parentCategory
     * @return \App\Model\Category\LinkedCategory\LinkedCategory[]
     */
    public function getAllByParentCategory(Category $parentCategory): array
    {
        return $this->getLinkedCategoryRepository()->findBy(['parentCategory' => $parentCategory], ['position' => 'asc']);
    }
}
