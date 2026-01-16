<?php

use Doctrine\ORM\Mapping as ORM;

class Foo {
    /**
     * @var bool
     */
    #[ORM\Column(options: ['default' => true], type: 'boolean')]
    protected $recalculateVisibility;
}
