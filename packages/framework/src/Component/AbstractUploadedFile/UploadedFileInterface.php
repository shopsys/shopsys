<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

interface UploadedFileInterface
{
    public function getFilename(): string;

    /**
     * @param mixed $name
     */
    public function setName($name): void;

    /**
     * @param mixed $slug
     */
    public function setSlug($slug): void;
}
