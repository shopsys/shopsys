<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'product_additional_service_domains')]
#[ORM\Entity]
class ProductAdditionalServiceDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productAdditionalServiceDomains')]
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'additional_service_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: AdditionalService::class)]
    protected $additionalService;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @param int $domainId
     */
    public function __construct(Product $product, AdditionalService $additionalService, $domainId)
    {
        $this->product = $product;
        $this->additionalService = $additionalService;
        $this->domainId = $domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService
     */
    public function getAdditionalService()
    {
        return $this->additionalService;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }
}
