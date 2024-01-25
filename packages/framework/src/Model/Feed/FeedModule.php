<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="feed_modules")
 * @ORM\Entity
 */
class FeedModule
{
    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @ORM\Id
     */
    protected $name;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $domainId;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $scheduled;

    /**
     * @param string $name
     * @param int $domainId
     */
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

    /**
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this->scheduled;
    }
}
