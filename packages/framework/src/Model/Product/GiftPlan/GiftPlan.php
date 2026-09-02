<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'gift_plans')]
#[ORM\Index(columns: ['domain_id', 'valid_from', 'valid_to'])]
#[ORM\Index(columns: ['gift_product_id'])]
#[ORM\Entity]
class GiftPlan implements DomainSeparatedEntityInterface
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
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid {
        set {
            $this->uuid = $value ?: Uuid::uuid4()->toString();
        }
    }

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
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Product>
     */
    #[ORM\JoinTable(name: 'gift_plan_main_products')]
    #[ORM\JoinColumn(name: 'gift_plan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'main_product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: Product::class)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $mainProducts;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'gift_product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $giftProduct;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $validFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $validTo;

    public function __construct(GiftPlanData $giftPlanData)
    {
        $this->uuid = $giftPlanData->uuid;
        $this->mainProducts = new ArrayCollection();
        $this->domainId = $giftPlanData->domainId;
        $this->setData($giftPlanData);
    }

    public function edit(GiftPlanData $giftPlanData): void
    {
        $this->setData($giftPlanData);
    }

    protected function setData(GiftPlanData $giftPlanData): void
    {
        $this->name = $giftPlanData->name;
        $this->validFrom = $giftPlanData->validFrom;
        $this->validTo = $giftPlanData->validTo;
        $this->giftProduct = $giftPlanData->giftProduct;
        $this->setMainProducts($giftPlanData->mainProducts);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $mainProducts
     */
    protected function setMainProducts($mainProducts): void
    {
        $this->mainProducts->clear();

        foreach ($mainProducts as $mainProduct) {
            $this->mainProducts->add($mainProduct);
        }
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
    public function getUuid()
    {
        return $this->uuid;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getMainProducts()
    {
        return $this->mainProducts->getValues();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getGiftProduct()
    {
        return $this->giftProduct;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getValidTo()
    {
        return $this->validTo;
    }
}
