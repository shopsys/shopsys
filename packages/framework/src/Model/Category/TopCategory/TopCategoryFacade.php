<?php

namespace Shopsys\FrameworkBundle\Model\Category\TopCategory;

use Doctrine\ORM\EntityManagerInterface;

class TopCategoryFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryRepository
     */
    protected $topCategoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFactoryInterface
     */
    protected $topCategoryFactory;

    public function __construct(
        EntityManagerInterface $em,
        TopCategoryRepository $topCategoryRepository,
        TopCategoryFactoryInterface $topCategoryFactory
    ) {
        $this->em = $em;
        $this->topCategoryRepository = $topCategoryRepository;
        $this->topCategoryFactory = $topCategoryFactory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllCategoriesByDomainId(int $domainId): array
    {
        $topCategories = $this->topCategoryRepository->getAllByDomainId($domainId);

        return $this->getCategoriesFromTopCategories($topCategories);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesByDomainId(int $domainId): array
    {
        $topCategories = $this->topCategoryRepository->getVisibleByDomainId($domainId);

        return $this->getCategoriesFromTopCategories($topCategories);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory[] $topCategories
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    protected function getCategoriesFromTopCategories($topCategories): array
    {
        $categories = [];

        foreach ($topCategories as $topCategory) {
            $categories[] = $topCategory->getCategory();
        }

        return $categories;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    public function saveTopCategoriesForDomain(int $domainId, array $categories): void
    {
        $oldTopCategories = $this->topCategoryRepository->getAllByDomainId($domainId);
        foreach ($oldTopCategories as $oldTopCategory) {
            $this->em->remove($oldTopCategory);
        }
        $this->em->flush($oldTopCategories);

        $topCategories = [];
        $position = 1;
        foreach ($categories as $category) {
            $topCategory = $this->topCategoryFactory->create($category, $domainId, $position++);
            $this->em->persist($topCategory);
            $topCategories[] = $topCategory;
        }
        $this->em->flush($topCategories);
    }
}
