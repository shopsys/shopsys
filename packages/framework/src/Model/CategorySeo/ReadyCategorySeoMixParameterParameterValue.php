<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'ready_category_seo_mix_parameter_parameter_values')]
#[ORM\Entity]
class ReadyCategorySeoMixParameterParameterValue
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'ready_category_seo_mix_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ReadyCategorySeoMix::class, inversedBy: 'readyCategorySeoMixParameterParameterValues', cascade: ['persist', 'remove'])]
    protected $readyCategorySeoMix;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'parameter_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Parameter::class)]
    protected $parameter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'parameter_value_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ParameterValue::class)]
    protected $parameterValue;

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
    public function setReadyCategorySeoMix($readyCategorySeoMix): void
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
