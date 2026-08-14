<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'friendly_urls')]
#[ORM\Index(columns: ['route_name', 'entity_id'])]
#[ORM\Index(columns: ['route_name', 'entity_id', 'domain_id', 'main'])]
#[ORM\Entity]
class FriendlyUrl implements DomainSeparatedEntityInterface
{
    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $routeName;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $entityId;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    protected $domainId;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text')]
    #[ORM\Id]
    protected $slug;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $main;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $redirectTo;

    /**
     * @var int|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $redirectCode;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $lastModification;

    /**
     * @param string $routeName
     * @param int $entityId
     * @param int $domainId
     * @param string $slug
     */
    public function __construct(
        $routeName,
        $entityId,
        $domainId,
        $slug,
    ) {
        $this->routeName = $routeName;
        $this->entityId = $entityId;
        $this->domainId = $domainId;
        $this->slug = FriendlyUrlSlugNormalizer::normalize($slug);
        $this->main = false;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return int
     */
    #[Override]
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return bool
     */
    public function isMain()
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

    /**
     * @param string|null $redirectTo
     */
    public function setRedirectTo($redirectTo): void
    {
        $this->redirectTo = $redirectTo;
    }

    /**
     * @param int|null $redirectCode
     */
    public function setRedirectCode($redirectCode): void
    {
        $this->redirectCode = $redirectCode;
    }

    /**
     * @param \DateTimeImmutable|null $lastModification
     */
    public function setLastModification($lastModification): void
    {
        $this->lastModification = $lastModification;
    }

    /**
     * @return string|null
     */
    public function getRedirectTo()
    {
        return $this->redirectTo;
    }

    /**
     * @return int|null
     */
    public function getRedirectCode()
    {
        return $this->redirectCode;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastModification()
    {
        return $this->lastModification;
    }
}
