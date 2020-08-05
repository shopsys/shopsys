<?php

class Foo
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $recalculateVisibility;

    /*
     * @ORM\Column(type="boolean", options={"default"}) annotation is forbidden, do not use it in this method
     */
    public function method(): void
    {

    }
}
