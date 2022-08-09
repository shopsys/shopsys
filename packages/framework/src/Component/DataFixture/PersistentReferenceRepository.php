<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\PersistentReferenceNotFoundException;

class PersistentReferenceRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference>
     */
    protected function getReferenceRepository(): EntityRepository
    {
        return $this->em->getRepository(PersistentReference::class);
    }

    /**
     * @param string $referenceName
     * @return \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference|null
     */
    public function findByReferenceName(string $referenceName): ?PersistentReference
    {
        return $this->getReferenceRepository()->find(['referenceName' => $referenceName]);
    }

    /**
     * @param string $referenceName
     * @return \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference
     */
    public function getByReferenceName(string $referenceName): PersistentReference
    {
        $reference = $this->findByReferenceName($referenceName);
        if ($reference === null) {
            throw new PersistentReferenceNotFoundException($referenceName);
        }
        return $reference;
    }
}
