<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

class TransportGroupData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var int
     */
    public $position;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var string|null
     */
    public $uuid;

    public function __construct()
    {
        $this->name = [];
        $this->position = 0;
    }
}
