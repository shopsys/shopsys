<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Author;

class BlogArticleAuthorData
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var array<string, string|null>
     */
    public $jobTitles;

    /**
     * @var array<string, string|null>
     */
    public $descriptions;

    /**
     * @var string|null
     */
    public $uuid;

    public function __construct()
    {
        $this->jobTitles = [];
        $this->descriptions = [];
    }
}
