<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData;

/**
 * @ORM\Table(name="parameter_values")
 * @ORM\Entity
 */
class ParameterValue extends BaseParameterValue
{
    /**
     * @var string|null
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $rgbHex;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    protected $colourIcon;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterData
     */
    public function __construct(ParameterValueData $parameterData)
    {
        parent::__construct($parameterData);

        $this->rgbHex = $parameterData->rgbHex;
        $this->colourIcon = $parameterData->colourIcon;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterData
     */
    public function edit(ParameterValueData $parameterData)
    {
        parent::edit($parameterData);

        $this->rgbHex = $parameterData->rgbHex;
    }

    /**
     * @return string|null
     */
    public function getRgbHex(): ?string
    {
        return $this->rgbHex;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    public function getColourIcon(): UploadedFileData
    {
        return $this->colourIcon;
    }
}
