<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Webmozart\Assert\Assert;

/**
 * @ORM\Table(name="parameter_values")
 * @ORM\Entity
 */
class ParameterValue
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @var string|null
     * @ORM\Column(type="decimal", precision=20, scale=6, nullable=true)
     */
    protected $numericValue;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $locale;

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
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData $parameterData
     */
    public function __construct(ParameterValueData $parameterData)
    {
        $this->text = $parameterData->text;
        $this->numericValue = $parameterData->numericValue;
        $this->locale = $parameterData->locale;
        $this->uuid = $parameterData->uuid ?: Uuid::uuid4()->toString();
        $this->rgbHex = $parameterData->rgbHex;
        $this->colourIcon = $parameterData->colourIcon;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData $parameterData
     */
    public function edit(ParameterValueData $parameterData)
    {
        $this->text = $parameterData->text;
        $this->numericValue = $parameterData->numericValue;
        $this->rgbHex = $parameterData->rgbHex;
        $this->colourIcon = $parameterData->colourIcon;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string|null
     */
    public function getRgbHex()
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

    /**
     * @return string|null
     */
    public function getNumericValue()
    {
        return $this->numericValue;
    }

    /**
     * @param string $numericValue
     */
    public function setNumericValue($numericValue)
    {
        Assert::numeric($numericValue);

        $this->numericValue = $numericValue;
    }
}
