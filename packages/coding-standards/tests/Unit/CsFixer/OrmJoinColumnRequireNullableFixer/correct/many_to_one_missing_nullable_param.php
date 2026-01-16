<?php

use Doctrine\ORM\Mapping as ORM;

class Bar
{
    /**
     * @var \StdObject
     */
    #[ORM\ManyToOne(targetEntity: StdObject::class)]
    #[ORM\JoinColumn(name: 'std_id', referencedColumnName: 'id', nullable: true)]
    private $foo4;
}
