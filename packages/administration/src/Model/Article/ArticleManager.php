<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Article;

use Shopsys\Administration\Component\Admin\AbstractDtoManager;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

class ArticleManager extends AbstractDtoManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory $articleDataFactory
     */
    public function __construct(
        protected readonly ArticleFacade $articleFacade,
        protected readonly ArticleDataFactory $articleDataFactory,
    ) {
    }

    /**
     * @return string
     */
    public function getSubjectClass(): string
    {
        return Article::class;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\ArticleData
     */
    public function createDataObject(): ArticleData
    {
        return $this->articleDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function doCreate(AdminIdentifierInterface $dataObject): Article
    {
        return $this->articleFacade->create($dataObject);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article $object
     */
    public function doDelete(object $object): void
    {
        $this->articleFacade->delete($object->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
        return $this->articleFacade->edit($dataObject->getId(), $dataObject);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article $entity
     * @return \Shopsys\Administration\Component\Security\AdminIdentifierInterface
     */
    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
        return $this->articleDataFactory->createFromArticle($entity);
    }
}
