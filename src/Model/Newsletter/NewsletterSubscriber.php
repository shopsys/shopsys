<?php

declare(strict_types=1);

namespace App\Model\Newsletter;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber as BaseNewsletterSubscriber;

/**
 * @ORM\Table(name="newsletter_subscribers")
 * @ORM\Entity
 */
class NewsletterSubscriber extends BaseNewsletterSubscriber
{
    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $updatedAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $deleted;

    /**
     * @param string $email
     * @param \DateTimeImmutable $createdAt
     * @param int $domainId
     */
    public function __construct(string $email, DateTimeImmutable $createdAt, int $domainId)
    {
        parent::__construct($email, $createdAt, $domainId);

        $this->deleted = false;
        $this->updatedAt = $createdAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeImmutable $value
     */
    public function setUpdatedAt(\DateTimeImmutable $value): void
    {
        $this->updatedAt = $value;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $value
     */
    public function setDeleted(bool $value)
    {
        $this->deleted = $value;
    }
}
