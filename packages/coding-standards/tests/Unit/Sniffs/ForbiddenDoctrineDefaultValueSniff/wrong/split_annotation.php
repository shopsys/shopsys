<?php

class Foo {

    /**
     * @ORM\Column(
     *     name="status",
     *     type="boolean",
     *     options={
     *      "default": 0
     *     }
     * )
     */
    protected $recalculateVisibility;
}
