<?php

use Doctrine\ORM\Mapping as ORM;

class Bar
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private $number;
}
