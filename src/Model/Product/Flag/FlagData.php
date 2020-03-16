<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData as BaseFlagData;

class FlagData extends BaseFlagData
{
    /**
     * @var bool
     */
    public $sale;

    public function __construct()
    {
        parent::__construct();
        $this->sale = false;
    }
}
