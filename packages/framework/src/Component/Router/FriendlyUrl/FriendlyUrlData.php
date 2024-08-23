<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $slug;

    /**
     * @var int|null
     */
    public $entityId;

    /**
     * @var string|null
     */
    public $redirectTo;

    /**
     * @var int|null
     */
    public $redirectCode;

    /**
     * @var \DateTime|null
     */
    public $lastModification;
}
