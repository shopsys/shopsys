<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

class WatchdogData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public $product;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var \DateTimeImmutable|null
     */
    public $createdAt;

    /**
     * @var \DateTimeImmutable|null
     */
    public $updatedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    public $validUntil;
}
