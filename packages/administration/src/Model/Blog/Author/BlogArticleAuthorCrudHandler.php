<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Blog\Author;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorData;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorFacade;
use Webmozart\Assert\Assert;

class BlogArticleAuthorCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly BlogArticleAuthorFacade $blogArticleAuthorFacade,
        protected readonly BlogArticleAuthorDataFactory $blogArticleAuthorDataFactory,
    ) {
    }

    #[Override]
    public function getById(int $id): Presentable
    {
        return $this->blogArticleAuthorFacade->getById($id);
    }

    #[Override]
    public function createData(): object
    {
        return $this->blogArticleAuthorDataFactory->create();
    }

    #[Override]
    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, BlogArticleAuthorData::class);

        return $this->blogArticleAuthorFacade->create($data);
    }

    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, BlogArticleAuthor::class);

        return $this->blogArticleAuthorDataFactory->createFromBlogArticleAuthor($entity);
    }

    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, BlogArticleAuthor::class);
        Assert::isInstanceOf($data, BlogArticleAuthorData::class);

        $this->blogArticleAuthorFacade->edit($entity->getId(), $data);
    }

    #[Override]
    public function delete(object $entity): void
    {
        Assert::isInstanceOf($entity, BlogArticleAuthor::class);

        $this->blogArticleAuthorFacade->deleteById($entity->getId());
    }
}
