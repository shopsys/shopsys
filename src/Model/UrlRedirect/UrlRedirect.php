<?php

declare(strict_types=1);


namespace App\Model\UrlRedirect;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="url_redirect")
 * @ORM\Entity
 */
class UrlRedirect
{
    /**
     * @ORM\Column(type="text", unique=true)
     * @ORM\Id
     * @var string
     */
    private string $oldUrl;

    /**
     * @ORM\Column(type="text", unique=true)
     * @var string
     */
    private string $newUrl;

    /**
     * @param \App\Model\UrlRedirect\UrlRedirectData $urlRedirectData
     */
    public function __construct(UrlRedirectData $urlRedirectData)
    {
        $this->oldUrl = $urlRedirectData->oldUrl;
        $this->newUrl = $urlRedirectData->newUrl;
    }

    /**
     * @return string
     */
    public function getOldUrl(): string
    {
        return $this->oldUrl;
    }

    /**
     * @return string
     */
    public function getNewUrl(): string
    {
        return $this->newUrl;
    }
}
