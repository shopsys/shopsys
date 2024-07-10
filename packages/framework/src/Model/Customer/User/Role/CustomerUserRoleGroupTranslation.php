<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="customer_user_role_group_translations")
 * @ORM\Entity
 */
class CustomerUserRoleGroupTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     * @Prezent\Translatable(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup")
     */
    protected $translatable;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
