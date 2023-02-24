<?php

namespace Shopsys\FrameworkBundle\Component\FileUpload;

class ImageUploadData
{
    /**
     * @var string[]
     */
    public array $uploadedFiles = [];

    /**
     * @var string[][]
     */
    public array $uploadedFilenames = [[]];

    /**
     * @var array<int, array<string,string>>
     */
    public array $namesIndexedByImageIdAndLocale = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public array $imagesToDelete = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public array $orderedImages = [];
}
