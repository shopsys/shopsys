<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'additional_service_domains')]
#[ORM\UniqueConstraint(name: 'additional_service_domain', columns: ['additional_service_id', 'domain_id'])]
#[ORM\Entity]
class AdditionalServiceDomain
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
     * @var \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AdditionalService::class, inversedBy: 'domains')]
    protected $additionalService;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $enabled;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $showInFeeds;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $useProductVatRate;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: Vat::class)]
    protected $vat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6)]
    protected $price;

    public function __construct(AdditionalService $additionalService, int $domainId)
    {
        $this->additionalService = $additionalService;
        $this->domainId = $domainId;
        $this->enabled = true;
        $this->showInFeeds = true;
        $this->useProductVatRate = true;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isShownInFeeds()
    {
        return $this->showInFeeds;
    }

    /**
     * @param bool $showInFeeds
     */
    public function setShowInFeeds($showInFeeds): void
    {
        $this->showInFeeds = $showInFeeds;
    }

    /**
     * @return bool
     */
    public function isProductVatRateUsed()
    {
        return $this->useProductVatRate;
    }

    /**
     * @param bool $useProductVatRate
     */
    public function setUseProductVatRate($useProductVatRate): void
    {
        $this->useProductVatRate = $useProductVatRate;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null $vat
     */
    public function setVat($vat): void
    {
        $this->vat = $vat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }
}
