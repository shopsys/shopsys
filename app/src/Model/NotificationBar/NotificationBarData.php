<?php

declare(strict_types=1);

namespace App\Model\NotificationBar;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;

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

    public function __construct()
    {
        $this->image = new ImageUploadData();
    }
}
