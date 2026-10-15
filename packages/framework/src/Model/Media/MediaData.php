<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Media;

class MediaData
{
    /**
     * @var string|null
     */
    public $title;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var int|null
     */
    public $width;

    /**
     * @var int|null
     */
    public $height;
}
