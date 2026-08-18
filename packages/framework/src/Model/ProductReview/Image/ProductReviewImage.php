<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Image;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableChild;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableParentProperty;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[LoggableChild(Loggable::STRATEGY_INCLUDE_ALL)]
#[ORM\Table(name: 'product_review_images')]
#[ORM\Entity]
class ProductReviewImage
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
     * @var \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview
     */
    #[AsMcpColumn]
    #[LoggableParentProperty]
    #[ORM\JoinColumn(name: 'product_review_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: ProductReview::class, inversedBy: 'images')]
    protected $productReview;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $position;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $rejectionReason;

    public function __construct(
        ProductReview $productReview,
        ProductReviewImageData $productReviewImageData,
        int $position,
    ) {
        $this->productReview = $productReview;
        $productReview->addImage($this);
        $this->position = $position;

        $this->setData($productReviewImageData);
    }

    public function edit(ProductReviewImageData $productReviewImageData): void
    {
        $this->setData($productReviewImageData);
    }

    protected function setData(ProductReviewImageData $productReviewImageData): void
    {
        $this->rejectionReason = TransformStringHelper::emptyToNull($productReviewImageData->rejectionReason);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview
     */
    public function getProductReview()
    {
        return $this->productReview;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string|null
     */
    public function getRejectionReason()
    {
        return $this->rejectionReason;
    }

    /**
     * @return bool
     */
    public function isRejected()
    {
        return $this->rejectionReason !== null;
    }
}
