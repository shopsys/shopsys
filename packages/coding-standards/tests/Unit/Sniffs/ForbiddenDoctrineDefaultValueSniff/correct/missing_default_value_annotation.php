<?php

use Doctrine\ORM\Mapping as ORM;

class Foo {
    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $recalculateVisibility;
}
