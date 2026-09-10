<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\AdditionalService;

class AdditionalServiceQueryDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $uuid;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $catnum;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var int
     */
    public $deliveryDaysExtension;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public $price;
}
