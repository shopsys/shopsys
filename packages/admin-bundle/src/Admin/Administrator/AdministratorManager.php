<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Admin\Administrator;

use Shopsys\AdminBundle\Component\Admin\AbstractDtoManager;
use Shopsys\AdminBundle\Component\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use App\Model\Administrator\AdministratorDataFactory;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Contracts\Service\Attribute\Required;

class AdministratorManager extends AbstractDtoManager
{
    #[Required]
    public AdministratorFacade $administratorFacade;

    #[Required]
    public AdministratorDataFactory $administratorDataFactory;

    public function getSubjectClass()
    {
        return Administrator::class;
    }

    public function createDataObject()
    {
        return $this->administratorDataFactory->create();
    }

    public function doCreate(AdminIdentifierInterface $dataObject): object
    {
        return $this->administratorFacade->create($dataObject);
    }

    public function doDelete(object $object)
    {
        $this->administratorFacade->delete($object->getId());
    }

    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
        return $this->administratorFacade->edit($dataObject->getId(), $dataObject);
    }

    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
        return $this->administratorDataFactory->createFromAdministrator($entity);
    }


}