<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class FlagData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var string|null
     */
    public $rgbColor;

    /**
     * @var bool
     */
    public $visible;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $urls;

    public function __construct()
    {
        $this->name = [];
        $this->visible = false;
        $this->rgbColor = '';
        $this->urls = new UrlListData();
    }
}
