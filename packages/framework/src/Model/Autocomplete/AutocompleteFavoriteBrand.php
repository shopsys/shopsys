<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'autocomplete_favorite_brands')]
#[ORM\Entity]
class AutocompleteFavoriteBrand implements OrderableEntityInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Brand::class)]
    #[ORM\Id]
    protected $brand;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    protected $domainId;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $position;

    public function __construct(Brand $brand, int $domainId, int $position)
    {
        $this->brand = $brand;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    #[Override]
    public function setPosition($position): void
    {
        $this->position = $position;
    }
}
