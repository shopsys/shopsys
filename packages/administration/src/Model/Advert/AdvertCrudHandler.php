<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Advert;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertData;
use Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactory;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Webmozart\Assert\Assert;

class AdvertCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly AdvertFacade $advertFacade,
        protected readonly AdvertDataFactory $advertDataFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Advert
    {
        return $this->advertFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        $advertData = $this->advertDataFactory->create();
        $advertData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        return $advertData;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Advert
    {
        Assert::isInstanceOf($data, AdvertData::class);

        return $this->advertFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(Presentable $entity): object
    {
        Assert::isInstanceOf($entity, Advert::class);

        return $this->advertDataFactory->createFromAdvert($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(Presentable $entity, object $data): void
    {
        Assert::isInstanceOf($entity, Advert::class);
        Assert::isInstanceOf($data, AdvertData::class);

        $this->advertFacade->edit($entity->getId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(Presentable $entity): void
    {
        Assert::isInstanceOf($entity, Advert::class);

        $this->advertFacade->delete($entity->getId());
    }
}
