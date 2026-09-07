<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributes;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'flag_domains')]
#[ORM\UniqueConstraint(name: 'flag_domain', columns: ['flag_id', 'domain_id'])]
#[ORM\Entity]
class FlagDomain
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'flag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Flag::class, inversedBy: 'domains')]
    protected $flag;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributes
     */
    #[AsMcpColumn]
    #[ORM\Embedded(class: SeoAttributes::class)]
    protected $seo;

    /**
     * @param int $domainId
     */
    public function __construct(Flag $flag, $domainId)
    {
        $this->flag = $flag;
        $this->domainId = $domainId;
        $this->seo = new SeoAttributes();
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\SeoAttributes
     */
    public function getSeoAttributes()
    {
        return $this->seo;
    }
}
