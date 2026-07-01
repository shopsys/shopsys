<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductQuestion;

class ProductQuestionData
{
    /**
     * @var string
     */
    public $customerName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $question;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public $product;

    /**
     * @var int
     */
    public $domainId;
}
