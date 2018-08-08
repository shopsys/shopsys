<?php

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use DateTimeImmutable;

interface NewsletterSubscriberFactoryInterface
{

    public function create(string $email, DateTimeImmutable $createdAt, int $domainId): NewsletterSubscriber;
}
