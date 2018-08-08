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

    /**
     * @param string $routeName
     * @param int $entityId
     * @param int $domainId
     * @param string $slug
     */
    public function __construct($routeName, $entityId, $domainId, $slug)
    {
        $this->routeName = $routeName;
        $this->entityId = $entityId;
        $this->domainId = $domainId;
        $this->slug = $slug;
        $this->main = false;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getEntityId()
    {
        return $this->entityId;
    }

    public function getDomainId()
    {
        return $this->domainId;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function isMain()
    {
        return $this->main;
    }

    /**
     * @param bool $main
     */
    public function setMain($main)
    {
        $this->main = $main;
    }
}
