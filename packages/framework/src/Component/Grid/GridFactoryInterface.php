<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

/**
 * @template T of array<string, mixed>
 */
interface GridFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid<T>
     */
    public function create();
}
