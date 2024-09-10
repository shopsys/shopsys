<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigLoader;

class CustomerUploadedFileConfigLoader extends UploadedFileConfigLoader
{
    /**
     * @param string $filename
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig
     */
    public function loadFromYaml(string $filename): CustomerUploadedFileConfig
    {
        parent::loadFromYaml($filename);

        return new CustomerUploadedFileConfig($this->uploadedFileEntityConfigsByClass);
    }
}
