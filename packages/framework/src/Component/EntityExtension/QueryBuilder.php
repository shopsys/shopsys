<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as BaseQueryBuilder;
use Override;

class QueryBuilder extends BaseQueryBuilder
{
    public function __construct(
        EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
        parent::__construct($em);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDQL(): string
    {
        return $this->entityNameResolver->resolveIn(parent::getDQL());
    }
}
