<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorFacade;
use Shopsys\FrameworkBundle\Model\Blog\Author\Exception\BlogArticleAuthorNotFoundException;
use Symfony\Component\Form\Exception\TransformationFailedException;

class BlogArticleAuthorIdToBlogArticleAuthorTransformer extends AbstractEntityIdToEntityTransformer
{
    public function __construct(
        protected readonly BlogArticleAuthorFacade $blogArticleAuthorFacade,
    ) {
    }

    #[Override]
    protected function getEntityId(object $entity): int
    {
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor $blogArticleAuthor */
        $blogArticleAuthor = $entity;

        return $blogArticleAuthor->getId();
    }

    #[Override]
    protected function getEntityById(int $entityId): BlogArticleAuthor
    {
        try {
            return $this->blogArticleAuthorFacade->getById($entityId);
        } catch (BlogArticleAuthorNotFoundException $exception) {
            throw new TransformationFailedException('Blog article author not found', 0, $exception);
        }
    }
}
