<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'personal_data_access_request')]
#[ORM\Entity]
class PersonalDataAccessRequest
{
    public const TYPE_DISPLAY = 'display';
    public const TYPE_EXPORT = 'export';

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string')]
    protected $email;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var string
     */
    #[AsMcpColumn(exposed: false)]
    #[ORM\Column(type: 'string', unique: true)]
    protected $hash;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50)]
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

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
