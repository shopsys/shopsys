<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\Bundle\DoctrineBundle\Mapping\ClassMetadataFactory as BaseClassMetadataFactory;
use Doctrine\ORM\EntityManagerInterface;
use Override;

class ClassMetadataFactory extends BaseClassMetadataFactory
{
    protected EntityManagerInterface $entityManager;

    #[Override]
    public function setEntityManager(EntityManagerInterface $em): void
    {
        $this->entityManager = $em;

        parent::setEntityManager($em);
    }

    #[Override]
    protected function newClassMetadataInstance(string $className): ClassMetadata
    {
        return new ClassMetadata(
            $className,
            $this->entityManager->getConfiguration()->getNamingStrategy(),
            $this->entityManager->getConfiguration()->getTypedFieldMapper(),
        );
    }
}
