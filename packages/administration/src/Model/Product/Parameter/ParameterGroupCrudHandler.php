<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Product\Parameter;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFacade;
use Webmozart\Assert\Assert;

class ParameterGroupCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly ParameterGroupFacade $parameterGroupFacade,
        protected readonly ParameterGroupDataFactory $parameterGroupDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Presentable
    {
        return $this->parameterGroupFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        return $this->parameterGroupDataFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, ParameterGroupData::class);

        return $this->parameterGroupFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, ParameterGroup::class);

        return $this->parameterGroupDataFactory->createFromParameterGroup($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, ParameterGroup::class);
        Assert::isInstanceOf($data, ParameterGroupData::class);

        $this->parameterGroupFacade->edit($entity->getId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(object $entity): void
    {
        Assert::isInstanceOf($entity, ParameterGroup::class);

        $this->parameterGroupFacade->deleteById($entity->getId());
    }
}
