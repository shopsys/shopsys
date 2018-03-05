<?php

namespace Shopsys\FrameworkBundle\Model\Category\TopCategory;

use Doctrine\ORM\EntityManager;

class TopCategoryFacade
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryRepository
     */
    private $topCategoryRepository;

    public function __construct(
        EntityManager $em,
        TopCategoryRepository $topCategoryRepository
    ) {
        $this->em = $em;
        $this->topCategoryRepository = $topCategoryRepository;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllCategoriesByDomainId($domainId)
    {
        $topCategories = $this->topCategoryRepository->getAllByDomainId($domainId);

        return $this->getCategoriesFromTopCategories($topCategories);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesByDomainId($domainId)
    {
        $topCategories = $this->topCategoryRepository->getVisibleByDomainId($domainId);

        return $this->getCategoriesFromTopCategories($topCategories);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory[] $topCategories
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    private function getCategoriesFromTopCategories($topCategories)
    {
        $categories = [];

        foreach ($topCategories as $topCategory) {
            $categories[] = $topCategory->getCategory();
        }

        return $categories;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    public function saveTopCategoriesForDomain($domainId, array $categories)
    {
        $oldTopCategories = $this->topCategoryRepository->getAllByDomainId($domainId);
        foreach ($oldTopCategories as $oldTopCategory) {
            $this->em->remove($oldTopCategory);
        }
        $this->em->flush($oldTopCategories);

        $topCategories = [];
        $position = 1;
        foreach ($categories as $category) {
            $topCategory = new TopCategory($category, $domainId, $position++);
            $this->em->persist($topCategory);
            $topCategories[] = $topCategory;
        }
        $this->em->flush($topCategories);
    }
}
