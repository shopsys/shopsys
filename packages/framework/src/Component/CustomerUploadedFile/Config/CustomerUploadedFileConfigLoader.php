<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config;

use Override;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigLoader;

class CustomerUploadedFileConfigLoader extends UploadedFileConfigLoader
{
    #[Override]
    public function loadFromYaml(string $filename): CustomerUploadedFileConfig
    {
        parent::loadFromYaml($filename);

        return new CustomerUploadedFileConfig($this->uploadedFileEntityConfigsByClass);
    }
}
