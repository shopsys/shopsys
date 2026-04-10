<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'zbozi_product_domains')]
#[ORM\Entity]
class ZboziProductDomain
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $show;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6, nullable: true)]
    protected $cpc;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6, nullable: true)]
    protected $cpcSearch;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    public function __construct(ZboziProductDomainData $zboziProductDomainData)
    {
        $this->setData($zboziProductDomainData);
    }

    public function edit(ZboziProductDomainData $zboziProductDomainData): void
    {
        $this->setData($zboziProductDomainData);
    }

    protected function setData(ZboziProductDomainData $zboziProductDomainData): void
    {
        $this->product = $zboziProductDomainData->product;
        $this->show = $zboziProductDomainData->show;
        $this->cpc = $zboziProductDomainData->cpc;
        $this->cpcSearch = $zboziProductDomainData->cpcSearch;
        $this->domainId = $zboziProductDomainData->domainId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return bool
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getCpc()
    {
        return $this->cpc;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getCpcSearch()
    {
        return $this->cpcSearch;
    }
}
