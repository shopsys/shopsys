<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\SeoCategory;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixData;
use App\Model\CategorySeo\ReadyCategorySeoMixDataFactory;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Shopsys\Administration\Component\Admin\AbstractDtoManager;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class SeoCategoryManager extends AbstractDtoManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $registry
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory
     */
    public function __construct(
        EntityNameResolver $entityNameResolver,
        ManagerRegistry $registry,
        PropertyAccessorInterface $propertyAccessor,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        protected readonly ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory,
    ) {
        parent::__construct($entityNameResolver, $registry, $propertyAccessor);
    }

    /**
     * @return string
     */
    public function getSubjectClass(): string
    {
        return ReadyCategorySeoMix::class;
    }

    /**
     * @return \App\Model\CategorySeo\ReadyCategorySeoMixData
     */
    public function createDataObject(): ReadyCategorySeoMixData
    {
        return $this->readyCategorySeoMixDataFactory->create();
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixData $dataObject
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function doCreate(AdminIdentifierInterface $dataObject): ReadyCategorySeoMix
    {
//        return $this->readyCategorySeoMixFacade->createOrEdit($dataObject);
        throw new Exception('Sorry jako');
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $object
     */
    public function doDelete(object $object): void
    {
        $this->readyCategorySeoMixFacade->delete($object->getId());
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixData $dataObject
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
//        return $this->readyCategorySeoMixFacade->createOrEdit($dataObject);
        throw new Exception('Sorry jako');
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $entity
     * @return \Shopsys\Administration\Component\Security\AdminIdentifierInterface
     */
    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
        return $this->readyCategorySeoMixDataFactory->createFromId($entity->getId());
    }
}
