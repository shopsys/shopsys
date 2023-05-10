<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use DateTime;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData as BaseFriendlyUrlData;

class FriendlyUrlData extends BaseFriendlyUrlData
{
    public string $slug;

    public int $entityId;

    public ?string $redirectTo = null;

    public ?int $redirectCode = null;

    public ?DateTime $lastModification = null;
}
