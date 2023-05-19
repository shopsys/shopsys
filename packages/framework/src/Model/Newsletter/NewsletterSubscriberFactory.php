<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class NewsletterSubscriberFactory implements NewsletterSubscriberFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param string $email
     * @param \DateTimeImmutable $createdAt
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber
     */
    public function create(string $email, DateTimeImmutable $createdAt, int $domainId): NewsletterSubscriber
    {
        $classData = $this->entityNameResolver->resolve(NewsletterSubscriber::class);

        return new $classData($email, $createdAt, $domainId);
    }
}
