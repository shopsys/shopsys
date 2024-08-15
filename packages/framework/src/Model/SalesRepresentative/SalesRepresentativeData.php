<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

class SalesRepresentativeData
{
    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var string|null
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $lastName;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $telephone;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;
}
