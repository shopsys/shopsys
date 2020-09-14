<?php

declare(strict_types=1);


namespace App\Model\UrlRedirect;

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

    public function __construct()
    {
        $this->oldUrl = null;
        $this->newUrl = null;
    }
}
