<?php

use Doctrine\ORM\Mapping as ORM;

class Foo {
    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected $recalculateVisibility;
}
