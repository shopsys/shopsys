<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload\Exception;

use Exception;

class UploadDirectoryNotFoundException extends Exception implements FileUploadException
{
}
