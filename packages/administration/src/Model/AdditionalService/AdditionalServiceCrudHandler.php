<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\AdditionalService;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceData;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceDataFactory;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Webmozart\Assert\Assert;

class AdditionalServiceCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly AdditionalServiceFacade $additionalServiceFacade,
        protected readonly AdditionalServiceDataFactory $additionalServiceDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): AdditionalService
    {
        return $this->additionalServiceFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        return $this->additionalServiceDataFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): AdditionalService
    {
        Assert::isInstanceOf($data, AdditionalServiceData::class);

        return $this->additionalServiceFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(Presentable $entity): object
    {
        Assert::isInstanceOf($entity, AdditionalService::class);

        return $this->additionalServiceDataFactory->createFromAdditionalService($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(Presentable $entity, object $data): void
    {
        Assert::isInstanceOf($entity, AdditionalService::class);
        Assert::isInstanceOf($data, AdditionalServiceData::class);

        $this->additionalServiceFacade->edit($entity->getId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(Presentable $entity): void
    {
        Assert::isInstanceOf($entity, AdditionalService::class);

        $this->additionalServiceFacade->deleteById($entity->getId());
    }
}
