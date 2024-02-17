<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Component\Admin;

use Doctrine\Common\Util\ClassUtils;
use Shopsys\AdminBundle\Component\AdminIdentifierInterface;

class AbstractAdmin extends \Sonata\AdminBundle\Admin\AbstractAdmin
{
    protected function createNewInstance(): object
    {
        /** @var \Shopsys\AdminBundle\Component\Admin\AbstractDtoManager $modelManager */
        $modelManager = $this->getModelManager();
        $dataObject = $modelManager->createDataObject();

        $this->setModelClass(ClassUtils::getClass($dataObject));

        return $dataObject;
    }

    public function generateDataObject($object): object
    {
        /** @var \Shopsys\AdminBundle\Component\Admin\AbstractDtoManager $modelManager */
        $modelManager = $this->getModelManager();
        return $modelManager->buildDataObjectForEdit($object);
    }

    public function toString(object $object): string
    {
        if ($object instanceof AdminIdentifierInterface && $object->getId() !== null) {
            $realClasss = $this->getModelManager()->getRealClass($object);
            $object = $this->getModelManager()->find($realClasss, $object->getId());
        }

        return parent::toString($object);
    }

    private function getSubjectClassName(): string
    {
        $reflectionClass = ClassUtils::newReflectionClass($this->getModelManager()->getSubjectClass());
        return mb_strtolower($reflectionClass->getShortName());
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_' . 'new_' . $this->getSubjectClassName();
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return $this->getSubjectClassName();
    }
}