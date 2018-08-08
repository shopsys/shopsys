<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;

class PersonalDataAccessRequestFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\String\HashGenerator
     */
    protected $hashGenerator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestRepository
     */
    protected $personalDataAccessRequestRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFactoryInterface
     */
    protected $personalDataAccessRequestFactory;

    public function __construct(
        EntityManagerInterface $em,
        HashGenerator $hashGenerator,
        PersonalDataAccessRequestRepository $personalDataAccessRequestRepository,
        PersonalDataAccessRequestFactoryInterface $personalDataAccessRequestFactory
    ) {
        $this->em = $em;
        $this->hashGenerator = $hashGenerator;
        $this->personalDataAccessRequestRepository = $personalDataAccessRequestRepository;
        $this->personalDataAccessRequestFactory = $personalDataAccessRequestFactory;
    }

    /**
     * @param int $domainId
     */
    public function createPersonalDataAccessRequest(
        PersonalDataAccessRequestData $personalDataAccessRequestData,
        $domainId
    ): \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest {
        $hash = $this->getUniqueHash();

        $personalDataAccessRequestData->hash = $hash;
        $personalDataAccessRequestData->createAt = new DateTime();
        $personalDataAccessRequestData->domainId = $domainId;

        $dataAccessRequest = $this->personalDataAccessRequestFactory->create($personalDataAccessRequestData);

        $this->em->persist($dataAccessRequest);
        $this->em->flush();

        return $dataAccessRequest;
    }

    /**
     * @param string $hash
     * @param int $domainId
     */
    public function findByHashAndDomainId($hash, $domainId): ?\Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest
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
