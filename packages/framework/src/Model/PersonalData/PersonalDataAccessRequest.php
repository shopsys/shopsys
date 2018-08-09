<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="personal_data_access_request")
 */
class PersonalDataAccessRequest
{
    const TYPE_DISPLAY = 'display';
    const TYPE_EXPORT = 'export';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     */
    protected $hash;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $type;

    public function __construct(PersonalDataAccessRequestData $personalDataAccessRequestData)
    {
        $this->email = $personalDataAccessRequestData->email;
        $this->createdAt = $personalDataAccessRequestData->createAt;
        $this->hash = $personalDataAccessRequestData->hash;
        $this->domainId = $personalDataAccessRequestData->domainId;
        $this->type = $personalDataAccessRequestData->type;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

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

    public function getType()
    {
        return $this->type;
    }
}
