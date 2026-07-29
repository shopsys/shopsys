<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

class ProductReviewData
{
    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public $product;

    /**
     * @var string
     */
    public $catnum;

    /**
     * @var string
     */
    public $productName;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem|null
     */
    public $orderItem;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public $customerUser;

    /**
     * @var string|null
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $lastName;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var bool
     */
    public $isAnonymous = false;

    /**
     * @var int|null
     */
    public $rating;

    /**
     * @var string|null
     */
    public $text;

    /**
     * @var string|null
     */
    public $ipAddress;

    /**
     * @var bool
     */
    public $isVerifiedPurchase = false;

    /**
     * @var string|null
     */
    public $status = ProductReviewStatusEnum::STATUS_PENDING;

    /**
     * @var string|null
     */
    public $rejectionReason;

    /**
     * @var string|null
     */
    public $responseText;

    /**
     * @var \DateTimeImmutable|null
     */
    public $responseCreatedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    public $createdAt;
}
