<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\EntityIdIsNotSetException;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\EntityNotFoundException;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\MethodGetIdDoesNotExistException;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\ObjectRequiredException;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\PersistentReferenceNotFoundException;

class PersistentReferenceFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceRepository $persistentReferenceRepository
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFactoryInterface $persistentReferenceFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PersistentReferenceRepository $persistentReferenceRepository,
        protected readonly PersistentReferenceFactoryInterface $persistentReferenceFactory,
    ) {
    }

    /**
     * @param string $name
     * @return object
     */
    public function getReference($name)
    {
        $persistentReference = $this->persistentReferenceRepository->getByReferenceName($name);
        $entity = $this->em->find($persistentReference->getEntityName(), $persistentReference->getEntityId());

        if ($entity === null) {
            throw new EntityNotFoundException($name);
        }

        return $entity;
    }

    /**
     * @param string $name
     * @param object $object
     */
    public function persistReference($name, $object)
    {
        if (!is_object($object)) {
            throw new ObjectRequiredException($object);
        }

        $entityName = get_class($object);

        if (!method_exists($object, 'getId')) {
            $message = 'Entity "' . $entityName . '" does not have a method "getId", which is necessary for persistent references.';

            throw new MethodGetIdDoesNotExistException($message);
        }

        $objectId = $object->getId();

        if ($objectId === null) {
            throw new EntityIdIsNotSetException($name, $object);
        }

        try {
            $persistentReference = $this->persistentReferenceRepository->getByReferenceName($name);
            $persistentReference->replace($entityName, $objectId);
        } catch (PersistentReferenceNotFoundException $ex) {
            $persistentReference = $this->persistentReferenceFactory->create($name, $entityName, $objectId);
            $this->em->persist($persistentReference);
        }
        $this->em->flush();
    }

    /**
     * @param string $name
     * @param object $object
     * @param int $domainId
     */
    public function persistReferenceForDomain(string $name, $object, int $domainId): void
    {
        $referenceName = $this->createDomainReferenceName($name, $domainId);
        $this->persistReference($referenceName, $object);
    }

    /**
     * @param string $name
     * @param int $domainId
     * @return object
     */
    public function getReferenceForDomain(string $name, int $domainId)
    {
        $referenceName = $this->createDomainReferenceName($name, $domainId);

        return $this->getReference($referenceName);
    }

    /**
     * @param string $name
     * @param int $domainId
     * @return string
     */
    protected function createDomainReferenceName(string $name, int $domainId): string
    {
        return sprintf('%s_%s', $name, $domainId);
    }
}
