<?php

declare(strict_types=1);


namespace App\Model\UrlRedirect;

use App\Component\Domain\Domain;
use App\Model\Domain\DomainHelper;

class UrlRedirectData
{
    /**
     * @var string|null
     */
    public ?string $oldUrl;

    /**
     * @var string|null
     */
    public ?string $newUrl;

    /**
     * @var int
     */
    public int $domainId;

    public function __construct()
    {
        $this->oldUrl = null;
        $this->newUrl = null;
        $this->domainId = Domain::FIRST_DOMAIN_ID;
    }
}
