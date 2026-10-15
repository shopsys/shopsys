<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Media;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaData;
use Shopsys\FrameworkBundle\Model\Media\MediaDataFactory;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Webmozart\Assert\Assert;

class MediaCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly MediaFacade $mediaFacade,
        protected readonly MediaDataFactory $mediaDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Media
    {
        return $this->mediaFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        return $this->mediaDataFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Media
    {
        Assert::isInstanceOf($data, MediaData::class);

        return $this->mediaFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(Presentable $entity): object
    {
        Assert::isInstanceOf($entity, Media::class);

        return $this->mediaDataFactory->createFromMedia($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(Presentable $entity, object $data): void
    {
        Assert::isInstanceOf($entity, Media::class);
        Assert::isInstanceOf($data, MediaData::class);

        $this->mediaFacade->edit($entity, $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(Presentable $entity): void
    {
        Assert::isInstanceOf($entity, Media::class);

        $this->mediaFacade->delete($entity);
    }
}
