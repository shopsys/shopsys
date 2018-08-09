<?php

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use DateTimeImmutable;

class NewsletterSubscriberFactory implements NewsletterSubscriberFactoryInterface
{
    public function create(string $email, DateTimeImmutable $createdAt, int $domainId): NewsletterSubscriber
    {
        return new NewsletterSubscriber($email, $createdAt, $domainId);
    }
}
