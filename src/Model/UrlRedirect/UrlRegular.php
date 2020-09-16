<?php

declare(strict_types=1);


namespace App\Model\UrlRedirect;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="url_regulars")
 * @ORM\Entity
 */
class UrlRegular
{
    /**
     * @ORM\Column(type="text")
     * @ORM\Id
     * @var string
     */
    private string $regular;

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
    private int $domainId;

    /**
     * @param string $regular
     * @param string $newUrl
     * @param int $domainId
     */
    public function __construct(string $regular, string $newUrl, int $domainId)
    {
        $this->regular = $regular;
        $this->newUrl = $newUrl;
        $this->domainId = $domainId;
    }

    /**
     * @return string
     */
    public function getRegular(): string
    {
        return $this->regular;
    }

    /**
     * @return string
     */
    public function getNewUrl(): string
    {
        return $this->newUrl;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
