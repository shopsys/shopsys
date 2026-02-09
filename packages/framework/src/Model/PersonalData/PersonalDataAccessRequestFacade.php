<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;

class PersonalDataAccessRequestFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly HashGenerator $hashGenerator,
        protected readonly PersonalDataAccessRequestRepository $personalDataAccessRequestRepository,
        protected readonly PersonalDataAccessRequestFactory $personalDataAccessRequestFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function createPersonalDataAccessRequest(
        PersonalDataAccessRequestData $personalDataAccessRequestData,
        int $domainId,
    ): PersonalDataAccessRequest {
        $hash = $this->getUniqueHash();

        $personalDataAccessRequestData->hash = $hash;
        $personalDataAccessRequestData->createAt = $this->clock->now();
        $personalDataAccessRequestData->domainId = $domainId;

        $dataAccessRequest = $this->personalDataAccessRequestFactory->create($personalDataAccessRequestData);

        $this->em->persist($dataAccessRequest);
        $this->em->flush();

        return $dataAccessRequest;
    }

    public function findByHashAndDomainId(string $hash, int $domainId): ?PersonalDataAccessRequest
    {
        return $this->personalDataAccessRequestRepository->findByHashAndDomainId($hash, $domainId);
    }

    protected function getUniqueHash(): string
    {
        do {
            $hash = $this->hashGenerator->generateHash(20);
        } while ($this->personalDataAccessRequestRepository->isHashUsed($hash));

        return $hash;
    }
}
