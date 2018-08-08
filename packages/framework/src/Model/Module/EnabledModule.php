<?php

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="enabled_modules")
 * @ORM\Entity
 */
class EnabledModule
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
