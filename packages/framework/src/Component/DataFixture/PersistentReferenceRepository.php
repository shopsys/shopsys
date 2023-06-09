<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\PersistentReferenceNotFoundException;

class PersistentReferenceRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getReferenceRepository()
    {
        return $this->em->getRepository(PersistentReference::class);
    }

    /**
     * @param string $referenceName
     * @return \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference|null
     */
    public function findByReferenceName($referenceName)
    {
        return $this->getReferenceRepository()->find(['referenceName' => $referenceName]);
    }

    /**
     * @param string $referenceName
     * @return \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference
     */
    public function getByReferenceName($referenceName)
    {
        $reference = $this->findByReferenceName($referenceName);
        if ($reference === null) {
            throw new PersistentReferenceNotFoundException($referenceName);
        }
        return $reference;
    }
}
