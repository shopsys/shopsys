<?php

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        EntityManagerInterface $em,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
        parent::__construct($em);
    }

    /**
     * {@inheritdoc}
     */
    public function getDQL(): string
    {
        return $this->entityNameResolver->resolveIn(parent::getDQL());
    }
}
