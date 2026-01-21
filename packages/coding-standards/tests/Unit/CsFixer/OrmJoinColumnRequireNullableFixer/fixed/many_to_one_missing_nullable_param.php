<?php

use Doctrine\ORM\Mapping as ORM;

class Bar
{
    /**
     * @var \StdObject
     */
    #[ORM\ManyToOne(targetEntity: StdObject::class)]
    #[ORM\JoinColumn(nullable: false, name: 'std_id', referencedColumnName: 'id')]
    private $foo3;
}
