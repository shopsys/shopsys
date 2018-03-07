<?php

namespace Shopsys\ShopBundle\Model\Gdpr;

use Shopsys\ShopBundle\Component\String\HashGenerator;

class GdprFacade
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Shopsys\ShopBundle\Component\String\HashGenerator
     */
    private $hashGenerator;

    /**
     * @var \Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequestRepository
     */
    private $gdprRepository;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em, HashGenerator $hashGenerator, PersonalDataAccessRequestRepository $gdprRepository)
    {
        $this->em = $em;
        $this->hashGenerator = $hashGenerator;
        $this->gdprRepository = $gdprRepository;
    }

    /**
     * @param string $email
     * @return \Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequest
     */
    public function createPersonalDataAccessRequest(PersonalDataAccessRequestData $personalDataAccessRequestData, $domainId)
    {
        $hash = $this->getUniqueHash();
        $personalDataAccessRequestData->hash = $hash;
        $personalDataAccessRequestData->createAt = new \DateTimeImmutable();
        $personalDataAccessRequestData->domainId = $domainId;
        $gdpr = PersonalDataAccessRequest::create($personalDataAccessRequestData);
        $this->em->persist($gdpr);
        $this->em->flush();

        return $gdpr;
    }

    /**
     * @param string $urlHash
     * @param int $domainId
     * @return \Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequest|null
     */
    public function findEmailByToken($urlHash, $domainId)
    {
        return $this->gdprRepository->findByHashAndDomainId($urlHash, $domainId);
    }

    /**
     * @return string
     */
    private function getUniqueHash()
    {
        do {
            $hash = $this->hashGenerator->generateHash(20);
        } while ($this->gdprRepository->isHashUsed($hash) !== 0);

        return $hash;
    }
}
