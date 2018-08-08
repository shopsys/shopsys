<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\ORM\EntityManagerInterface;

class PersistentReferenceRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getReferenceRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(PersistentReference::class);
    }

    public function findByReferenceName(string $referenceName): ?\Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference
    {
        return $this->getReferenceRepository()->find(['referenceName' => $referenceName]);
    }

    public function getByReferenceName(string $referenceName): \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference
    {
        $reference = $this->findByReferenceName($referenceName);
        if ($reference === null) {
            throw new \Shopsys\FrameworkBundle\Component\DataFixture\Exception\PersistentReferenceNotFoundException($referenceName);
        }
        return $reference;
    }
}
