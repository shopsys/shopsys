<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;

#[ORM\Table(name: 'google_product_domains')]
#[ORM\Entity]
class GoogleProductDomain
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $show;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    public function __construct(GoogleProductDomainData $googleProductDomainData)
    {
        $this->setData($googleProductDomainData);
    }

    public function edit(GoogleProductDomainData $googleProductDomainData)
    {
        $this->setData($googleProductDomainData);
    }

    protected function setData(GoogleProductDomainData $googleProductDomainData): void
    {
        $this->product = $googleProductDomainData->product;
        $this->show = $googleProductDomainData->show;
        $this->domainId = $googleProductDomainData->domainId;
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
}
