<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

/**
 * @ORM\Table(name="ready_category_seo_mix_parameter_parameter_values")
 * @ORM\Entity
 */
class ReadyCategorySeoMixParameterParameterValue
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMix
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\CategorySeo\ReadyCategorySeoMix", inversedBy="readyCategorySeoMixParameterParameterValues", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="ready_category_seo_mix_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $readyCategorySeoMix;

    /**
     * @var \App\Model\Product\Parameter\Parameter
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter")
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $parameter;

    /**
     * @var \App\Model\Product\Parameter\ParameterValue
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue")
     * @ORM\JoinColumn(name="parameter_value_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $parameterValue;

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     */
    public function __construct(
        Parameter $parameter,
        ParameterValue $parameterValue,
    ) {
        $this->parameter = $parameter;
        $this->parameterValue = $parameterValue;
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     */
    public function setReadyCategorySeoMix(ReadyCategorySeoMix $readyCategorySeoMix): void
    {
        $this->readyCategorySeoMix = $readyCategorySeoMix;
    }

    /**
     * @return \App\Model\Product\Parameter\Parameter
     */
    public function getParameter(): Parameter
    {
        return $this->parameter;
    }

    /**
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValue(): ParameterValue
    {
        return $this->parameterValue;
    }
}
