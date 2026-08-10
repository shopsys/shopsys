<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'feed_modules')]
#[ORM\Entity]
class FeedModule implements DomainSeparatedEntityInterface
{
    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    #[ORM\Id]
    protected $name;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    protected $domainId;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $scheduled;

    public function __construct(string $name, int $domainId)
    {
        $this->name = $name;
        $this->domainId = $domainId;
        $this->scheduled = false;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    #[Override]
    public function getDomainId()
    {
        return $this->domainId;
    }

    public function schedule(): void
    {
        $this->scheduled = true;
    }

    public function unschedule(): void
    {
        $this->scheduled = false;
    }

    public function isScheduled(): bool
    {
        return $this->scheduled;
    }
}
