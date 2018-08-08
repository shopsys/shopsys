<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="parameter_values")
 * @ORM\Entity
 */
class ParameterValue
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $text;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $locale;

    public function __construct(ParameterValueData $parameterData)
    {
        $this->text = $parameterData->text;
        $this->locale = $parameterData->locale;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function edit(ParameterValueData $parameterData)
    {
        $this->text = $parameterData->text;
    }
}
