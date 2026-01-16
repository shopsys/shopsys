<?php

use Doctrine\ORM\Mapping as ORM;

class Bar
{
    /**
     * @var \StdObject
     */
    #[ORM\OneToMany(targetEntity: StdObject::class)]
    private $foo1;
}
