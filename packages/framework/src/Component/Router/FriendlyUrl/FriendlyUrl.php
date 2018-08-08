<?php

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
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $routeName;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $entityId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $domainId;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @ORM\Id
     */
    protected $slug;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $main;

    public function __construct(string $routeName, int $entityId, int $domainId, string $slug)
    {
        $this->routeName = $routeName;
        $this->entityId = $entityId;
        $this->domainId = $domainId;
        $this->slug = $slug;
        $this->main = false;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getDomainId(): string
    {
        return $this->domainId;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function isMain(): bool
    {
        return $this->main;
    }

    public function setMain(bool $main): void
    {
        $this->main = $main;
    }
}
