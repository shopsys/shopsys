<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl as BaseFriendlyUrl;

/**
 * @ORM\Table(
 *     name="friendly_urls",
 *     indexes={
 *         @ORM\Index(columns={"route_name", "entity_id"})
 *     }
 * )
 * @ORM\Entity
 */
class FriendlyUrl extends BaseFriendlyUrl
{
    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $redirectTo = null;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $redirectCode = null;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $lastModification = null;

    /**
     * @param string|null $redirectTo
     */
    public function setRedirectTo(?string $redirectTo): void
    {
        $this->redirectTo = $redirectTo;
    }

    /**
     * @param int|null $redirectCode
     */
    public function setRedirectCode(?int $redirectCode): void
    {
        $this->redirectCode = $redirectCode;
    }

    /**
     * @param \DateTime|null $lastModification
     */
    public function setLastModification(?DateTime $lastModification): void
    {
        $this->lastModification = $lastModification;
    }

    /**
     * @return string|null
     */
    public function getRedirectTo(): ?string
    {
        return $this->redirectTo;
    }

    /**
     * @return int|null
     */
    public function getRedirectCode(): ?int
    {
        return $this->redirectCode;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastModification(): ?DateTime
    {
        return $this->lastModification;
    }
}
