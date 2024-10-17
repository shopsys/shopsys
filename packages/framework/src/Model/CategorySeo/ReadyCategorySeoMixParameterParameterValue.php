<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

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
     * @var \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity="Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix",
     *     inversedBy="readyCategorySeoMixParameterParameterValues",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(name="ready_category_seo_mix_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $readyCategorySeoMix;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter")
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $parameter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue")
     * @ORM\JoinColumn(name="parameter_value_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $parameterValue;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     */
    public function __construct(
        Parameter $parameter,
        ParameterValue $parameterValue,
    ) {
        $this->parameter = $parameter;
        $this->parameterValue = $parameterValue;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     */
    public function setReadyCategorySeoMix($readyCategorySeoMix)
    {
        $this->readyCategorySeoMix = $readyCategorySeoMix;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValue()
    {
        return $this->parameterValue;
    }
}
