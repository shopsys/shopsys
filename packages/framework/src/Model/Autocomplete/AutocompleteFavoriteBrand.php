<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

#[ORM\Table(name: 'autocomplete_favorite_brands')]
#[ORM\Entity]
class AutocompleteFavoriteBrand implements OrderableEntityInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Brand::class)]
    #[ORM\Id]
    protected $brand;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    protected $domainId;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $position;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param int $domainId
     * @param int $position
     */
    public function __construct(Brand $brand, int $domainId, int $position)
    {
        $this->brand = $brand;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    public function getBrand()
    {
        return $this->brand;
    }

    public function getDomainId()
    {
        return $this->domainId;
    }

    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    #[Override]
    public function setPosition($position)
    {
        $this->position = $position;
    }
}
