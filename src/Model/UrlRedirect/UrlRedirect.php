<?php

declare(strict_types=1);


namespace App\Model\UrlRedirect;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="url_redirects")
 * @ORM\Entity
 */
class UrlRedirect
{
    /**
     * @ORM\Column(type="text")
     * @ORM\Id
     * @var string
     */
    private string $oldUrl;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private string $newUrl;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected int $domainId;

    /**
     * @param \App\Model\UrlRedirect\UrlRedirectData $urlRedirectData
     */
    public function __construct(UrlRedirectData $urlRedirectData)
    {
        $this->oldUrl = $urlRedirectData->oldUrl;
        $this->newUrl = $urlRedirectData->newUrl;
        $this->domainId = $urlRedirectData->domainId;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
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
