<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class UrlListData
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public $toDelete;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public $mainFriendlyUrl;

    /**
     * Slugs of the URL addresses to be created
     *
     * @var string[]
     */
    public array $newUrls;

    public function __construct()
    {
        $this->toDelete = [];
        $this->mainFriendlyUrl = null;
        $this->newUrls = [];
    }
}
