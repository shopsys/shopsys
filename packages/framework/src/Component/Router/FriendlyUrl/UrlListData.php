<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

/**
 * URL addresses of an entity on a single domain, multidomain entities carry one instance per domain indexed by the domain ID
 */
class UrlListData
{
    public const FIELD_SLUG = 'slug';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public $toDelete;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public $mainFriendlyUrl;

    /**
     * @var array<int, array<string, string>>
     *
     * Format:
     * [
     *     ['slug' => 'new-slug'],
     *     ...
     * ]
     * @see \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade::saveUrlListFormData()
     */
    public array $newUrls;

    public function __construct()
    {
        $this->toDelete = [];
        $this->mainFriendlyUrl = null;
        $this->newUrls = [];
    }
}
