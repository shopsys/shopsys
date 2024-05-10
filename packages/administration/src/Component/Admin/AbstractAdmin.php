<?php

declare(strict_types=1);

namespace Shopsys\Administration\Component\Admin;

use Doctrine\Common\Util\ClassUtils;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin as BaseAbstractAdmin;

abstract class AbstractAdmin extends BaseAbstractAdmin
{
    /**
     * @return object
     */
    protected function createNewInstance(): object
    {
        /** @var \Shopsys\Administration\Component\Admin\AbstractDtoManager $modelManager */
        $modelManager = $this->getModelManager();
        $dataObject = $modelManager->createDataObject();

        $this->setModelClass(ClassUtils::getClass($dataObject));

        return $dataObject;
    }

    /**
     * @param object $object
     * @return object
     */
    public function generateDataObject(object $object): object
    {
        /** @var \Shopsys\Administration\Component\Admin\AbstractDtoManager $modelManager */
        $modelManager = $this->getModelManager();

        return $modelManager->buildDataObjectForEdit($object);
    }

    /**
     * @param object $object
     * @return string
     */
    public function toString(object $object): string
    {
        if ($object instanceof AdminIdentifierInterface && $object->getId() !== null) {
            /** @var \Shopsys\Administration\Component\Admin\AbstractDtoManager $modelManager */
            $modelManager = $this->getModelManager();
            $realClasss = $modelManager->getRealClass($object, false);
            $object = $modelManager->find($realClasss, $object->getId());
        }

        return parent::toString($object);
    }

    /**
     * @return string
     */
    protected function getSubjectClassName(): string
    {
        /** @var \Shopsys\Administration\Component\Admin\AbstractDtoManager $modelManager */
        $modelManager = $this->getModelManager();
        $reflectionClass = ClassUtils::newReflectionClass($modelManager->getSubjectClass());

        return mb_strtolower($reflectionClass->getShortName());
    }

    /**
     * @param bool $isChildAdmin
     * @return string
     */
    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_' . 'new_' . $this->getSubjectClassName();
    }

    /**
     * @param bool $isChildAdmin
     * @return string
     */
    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return $this->getSubjectClassName();
    }
}
