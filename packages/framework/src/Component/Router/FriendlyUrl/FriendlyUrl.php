<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="friendly_urls",
 *     indexes={
 *         @ORM\Index(columns={"route_name", "entity_id"})
 *     }
 * )
 * @ORM\Entity
 */
class FriendlyUrl
{
    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $routeName;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $entityId;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $domainId;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @ORM\Id
     */
    protected $slug;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $main;

    /**
     * @param string $routeName
     * @param int $entityId
     * @param int $domainId
     * @param string $slug
     */
    public function __construct(string $routeName, int $entityId, int $domainId, string $slug)
    {
        $this->routeName = $routeName;
        $this->entityId = $entityId;
        $this->domainId = $domainId;
        $this->slug = $slug;
        $this->main = false;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
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
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->main;
    }

    /**
     * @param bool $main
     */
    public function setMain($main): void
    {
        $this->main = $main;
    }
}
