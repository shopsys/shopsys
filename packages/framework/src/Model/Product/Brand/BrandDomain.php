<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributes;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'brand_domains')]
#[ORM\UniqueConstraint(name: 'brand_domain', columns: ['brand_id', 'domain_id'])]
#[ORM\Entity]
class BrandDomain
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'brand_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'domains')]
    protected $brand;

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
    public function __construct(Brand $brand, $domainId)
    {
        $this->brand = $brand;
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
