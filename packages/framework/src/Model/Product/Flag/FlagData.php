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

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy|null
     */
    public $promotionXy;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData[]
     */
    public $seo;

    public function __construct()
    {
        $this->name = [];
        $this->visible = false;
        $this->rgbColor = '';
        $this->urls = new UrlListData();
        $this->promotionXy = null;
        $this->seo = [];
    }
}
