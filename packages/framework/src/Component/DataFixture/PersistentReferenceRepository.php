<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\PersistentReferenceNotFoundException;

class PersistentReferenceRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getReferenceRepository(): EntityRepository
    {
        return $this->em->getRepository(PersistentReference::class);
    }

    public function findByReferenceName(
        string $referenceName,
    ): ?PersistentReference {
        return $this->getReferenceRepository()->find(['referenceName' => $referenceName]);
    }

    public function getByReferenceName(
        string $referenceName,
    ): PersistentReference {
        $reference = $this->findByReferenceName($referenceName);

        if ($reference === null) {
            throw new PersistentReferenceNotFoundException($referenceName);
        }

        return $reference;
    }
}
