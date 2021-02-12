<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Newsletter\Subscriber;

use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber;
use Tests\App\Test\TransactionFunctionalTestCase;

class NewsletterSubscriberPersistenceTest extends TransactionFunctionalTestCase
{
    public function testPersistence(): void
    {
        $newsletterSubscriber = new NewsletterSubscriber(
            'no-reply@shopsys.com',
            new DateTimeImmutable('2018-02-06 15:15:48'),
            1
        );

        $this->em->persist($newsletterSubscriber);
        $this->em->flush();
        $this->em->clear();

        $found = $this->em->createQueryBuilder()
        ->select('ns')
        ->from(NewsletterSubscriber::class, 'ns')
        ->where('ns.email = :email')
        ->andWhere('ns.domainId = :domainId')
        ->setParameters(['email' => 'no-reply@shopsys.com', 'domainId' => Domain::FIRST_DOMAIN_ID])
        ->getQuery()->getOneOrNullResult();

        Assert::assertEquals($newsletterSubscriber, $found);
        Assert::assertNotSame($newsletterSubscriber, $found);
    }
}
