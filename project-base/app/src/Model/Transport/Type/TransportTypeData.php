<?php

declare(strict_types=1);

namespace App\Model\Transport\Type;

class TransportTypeData
{
    /**
     * @var string|null
     */
    public $code;

    /**
     * @var string[]|null[]
     */
    public $names;

    public function __construct()
    {
        $this->names = [];
    }
}
