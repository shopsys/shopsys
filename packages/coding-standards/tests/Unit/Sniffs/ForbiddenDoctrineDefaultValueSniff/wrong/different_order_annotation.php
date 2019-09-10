<?php

class Foo {
    /**
     * @var bool
     *
     * @ORM\Column(options={"default" = true}, type="boolean")
     */
    protected $recalculateVisibility;
}
