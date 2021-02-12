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

    /**
     * @var string|null
     */
    public $akeneoCode;

    /**
     * @var string|null
     */
    public $noticeLowPrice;

    /**
     * @var string|null
     */
    public $noticeHighPrice;

    public function __construct()
    {
        parent::__construct();

        $this->sale = false;
        $this->rgbColor = '';
    }
}
