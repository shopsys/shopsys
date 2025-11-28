<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;

class PersonalDataAccessRequestFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestRepository $personalDataAccessRequestRepository
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFactory $personalDataAccessRequestFactory
     * @param \Psr\Clock\ClockInterface $clock
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly HashGenerator $hashGenerator,
        protected readonly PersonalDataAccessRequestRepository $personalDataAccessRequestRepository,
        protected readonly PersonalDataAccessRequestFactory $personalDataAccessRequestFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestData $personalDataAccessRequestData
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest
     */
    public function createPersonalDataAccessRequest(
        PersonalDataAccessRequestData $personalDataAccessRequestData,
        $domainId,
    ) {
        $hash = $this->getUniqueHash();

        $personalDataAccessRequestData->hash = $hash;
        $personalDataAccessRequestData->createAt = $this->clock->now();
        $personalDataAccessRequestData->domainId = $domainId;

        $dataAccessRequest = $this->personalDataAccessRequestFactory->create($personalDataAccessRequestData);

        $this->em->persist($dataAccessRequest);
        $this->em->flush();

        return $dataAccessRequest;
    }

    /**
     * @param string $hash
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest|null
     */
    public function findByHashAndDomainId($hash, $domainId)
    {
        return $this->personalDataAccessRequestRepository->findByHashAndDomainId($hash, $domainId);
    }

    /**
     * @return string
     */
    protected function getUniqueHash()
    {
        do {
            $hash = $this->hashGenerator->generateHash(20);
        } while ($this->personalDataAccessRequestRepository->isHashUsed($hash));

        return $hash;
    }
}
