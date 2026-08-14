<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix\Settings;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'phone_prefixes')]
#[ORM\UniqueConstraint(name: 'phone_prefixes_domain_code', columns: ['domain_id', 'code'])]
#[ORM\Entity]
class PhonePrefix implements DomainSeparatedEntityInterface
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

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
    #[ORM\Column(type: 'string', length: 2)]
    protected $code;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $isDefault;

    public function __construct(int $domainId, string $code, bool $isDefault = false)
    {
        $this->domainId = $domainId;
        $this->code = $code;
        $this->isDefault = $isDefault;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    #[Override]
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->isDefault;
    }
}
