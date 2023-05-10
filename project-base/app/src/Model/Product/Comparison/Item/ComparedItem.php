<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison\Item;

use App\Model\Product\Comparison\Comparison;
use App\Model\Product\Product;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="compared_items")
 * @ORM\Entity
 */
class ComparedItem
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @var \App\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Product $product;

    /**
     * @var \App\Model\Product\Comparison\Comparison
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Comparison\Comparison", inversedBy="items", cascade={"persist"})
     * @ORM\JoinColumn(name="comparison_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Comparison $comparison;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private DateTime $createdAt;

    /**
     * @param \App\Model\Product\Comparison\Comparison $comparison
     * @param \App\Model\Product\Product $product
     */
    public function __construct(Comparison $comparison, Product $product)
    {
        $this->comparison = $comparison;
        $this->product = $product;
        $this->createdAt = new DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Model\Product\Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return \App\Model\Product\Comparison\Comparison
     */
    public function getComparison(): Comparison
    {
        return $this->comparison;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
