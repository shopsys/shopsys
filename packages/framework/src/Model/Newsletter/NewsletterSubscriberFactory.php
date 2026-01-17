<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class NewsletterSubscriberFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(string $email, DateTimeImmutable $createdAt, int $domainId): NewsletterSubscriber
    {
        $entityClassName = $this->entityNameResolver->resolve(NewsletterSubscriber::class);

        return new $entityClassName($email, $createdAt, $domainId);
    }
}
