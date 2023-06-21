<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData as BaseParameterValueData;

class ParameterValueData extends BaseParameterValueData
{
    /**
     * @var string|null
     */
    public $rgbHex;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    public $colourIcon;
}
