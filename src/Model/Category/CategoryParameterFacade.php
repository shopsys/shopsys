<?php

declare(strict_types=1);

namespace App\Model\Category;

use Doctrine\ORM\EntityManagerInterface;

class CategoryParameterFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Category\CategoryParameterRepository
     */
    private $categoryParameterRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Category\CategoryParameterRepository $categoryParameterRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        CategoryParameterRepository $categoryParameterRepository
    ) {
        $this->em = $em;
        $this->categoryParameterRepository = $categoryParameterRepository;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Product\Parameter\Parameter[] $parameters
     */
    public function saveRelation(Category $category, array $parameters): void
    {
        $oldCategoryParameters = $this->categoryParameterRepository->getAllByCategory($category);
        $oldCategoryParametersById = [];
        foreach ($oldCategoryParameters as $oldCategoryParameter) {
            $oldCategoryParametersById[$oldCategoryParameter->getParameter()->getId()] = $oldCategoryParameter;
        }
        $catFlushAfterSaveRelation = false;
        foreach ($parameters as $parameter) {
            if (array_key_exists($parameter->getId(), $oldCategoryParametersById)) {
                unset($oldCategoryParametersById[$parameter->getId()]);
                continue;
            }
            $categoryParameter = new CategoryParameter($category, $parameter);
            $this->em->persist($categoryParameter);
            $catFlushAfterSaveRelation = true;
        }

        foreach ($oldCategoryParametersById as $oldCategoryParameter) {
            $this->em->remove($oldCategoryParameter);
            $catFlushAfterSaveRelation = true;
        }

        if ($catFlushAfterSaveRelation) {
            $this->em->flush();
        }
    }
}
