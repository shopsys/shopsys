<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Category;

use Doctrine\Persistence\ManagerRegistry;
use Shopsys\Administration\Component\Admin\AbstractDtoManager;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CategoryManager extends AbstractDtoManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $registry
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory $categoryDataFactory
     */
    public function __construct(
        EntityNameResolver $entityNameResolver,
        ManagerRegistry $registry,
        PropertyAccessorInterface $propertyAccessor,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly CategoryDataFactory $categoryDataFactory,
    ) {
        parent::__construct($entityNameResolver, $registry, $propertyAccessor);
    }

    /**
     * @return string
     */
    public function getSubjectClass(): string
    {
        return Category::class;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryData
     */
    public function createDataObject(): CategoryData
    {
        return $this->categoryDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function doCreate(AdminIdentifierInterface $dataObject): Category
    {
        return $this->categoryFacade->create($dataObject);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $object
     */
    public function doDelete(object $object): void
    {
        $this->categoryFacade->delete($object->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
        return $this->categoryFacade->edit($dataObject->getId(), $dataObject);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $entity
     * @return \Shopsys\Administration\Component\Security\AdminIdentifierInterface
     */
    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
        return $this->categoryDataFactory->createFromCategory($entity);
    }
}
