<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="autocomplete_favorite_products")
 * @ORM\Entity
 */
class AutocompleteFavoriteProduct implements OrderableEntityInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    protected $product;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $domainId;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $position;

    public function __construct(Product $product, int $domainId, int $position)
    {
        $this->product = $product;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
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
