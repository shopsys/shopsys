<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

class ComplaintItemData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem|null
     */
    public $orderItem;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public $product;

    /**
     * @var int|null
     */
    public $quantity;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileData
     */
    public $files;

    /**
     * @var string|null
     */
    public $productName;

    /**
     * @var string|null
     */
    public $catnum;
}
