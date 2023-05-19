<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ArticleFactory implements ArticleFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $data
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function create(ArticleData $data): Article
    {
        $classData = $this->entityNameResolver->resolve(Article::class);

        return new $classData($data);
    }
}
