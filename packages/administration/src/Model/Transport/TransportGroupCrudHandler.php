<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Transport;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroup;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroupData;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroupFacade;
use Webmozart\Assert\Assert;

class TransportGroupCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly TransportGroupFacade $transportGroupFacade,
        protected readonly TransportGroupDataFactory $transportGroupDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Presentable
    {
        return $this->transportGroupFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        return $this->transportGroupDataFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, TransportGroupData::class);

        return $this->transportGroupFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, TransportGroup::class);

        return $this->transportGroupDataFactory->createFromTransportGroup($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, TransportGroup::class);
        Assert::isInstanceOf($data, TransportGroupData::class);

        $this->transportGroupFacade->edit($entity, $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(object $entity): void
    {
        Assert::isInstanceOf($entity, TransportGroup::class);

        $this->transportGroupFacade->delete($entity);
    }
}
