<?php

namespace Shopsys\ShopBundle\Model\Gdpr;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="personal_data_access_request")
 */
class PersonalDataAccessRequest
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $hash;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $domainId;

    /**
     * Gdpr constructor.
     *
     * @param $email
     * @param $createdAt
     * @param $hash
     * @param $domainId
     */
    public function __construct(PersonalDataAccessRequestData $personalDataAccessRequestData)
    {
        $this->email = $personalDataAccessRequestData->email;
        $this->createdAt = $personalDataAccessRequestData->createAt;
        $this->hash = $personalDataAccessRequestData->hash;
        $this->domainId = $personalDataAccessRequestData->domainId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequestData $personalDataAccessRequestData
     * @return \Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequest
     */
    public static function create(PersonalDataAccessRequestData $personalDataAccessRequestData)
    {
        return new self($personalDataAccessRequestData);
    }
}
