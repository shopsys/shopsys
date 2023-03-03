<?php

declare(strict_types=1);

namespace App\Model\NotificationBar;

class NotificationBarData
{
    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var string|null
     */
    public $text;

    /**
     * @var \DateTime|null
     */
    public $validityFrom;

    /**
     * @var \DateTime|null
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
