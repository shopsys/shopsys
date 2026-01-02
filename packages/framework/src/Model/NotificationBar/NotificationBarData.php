<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

class NotificationBarData
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
     * @var string|null
     */
    public $text;

    /**
     * @var \DateTimeImmutable|null
     */
    public $validityFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    public $validityTo;

    /**
     * @var string
     */
    public $rgbColor;

    /**
     * @var bool|null
     */
    public $hidden;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;
}
