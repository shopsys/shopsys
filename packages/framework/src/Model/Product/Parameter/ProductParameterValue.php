<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="product_parameter_values")
 * @ORM\Entity
 */
class ProductParameterValue
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter")
     * @ORM\JoinColumn(nullable=false, name="parameter_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parameter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue")
     * @ORM\JoinColumn(name="value_id", referencedColumnName="id", nullable=false)
     */
    protected $value;

    public function __construct(
        Product $product,
        Parameter $parameter,
        ParameterValue $value
    ) {
        $this->product = $product;
        $this->parameter = $parameter;
        $this->value = $value;
    }

    public function getProduct(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }

    public function getParameter(): \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
    {
        return $this->parameter;
    }

    public function getValue(): \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
    {
        return $this->value;
    }
}
