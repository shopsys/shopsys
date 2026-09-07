<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Newsletter\NewsletterRepository;

use PHPUnit\Framework\Assert;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class GetAllEmailsDataIteratorMethodTest extends TransactionFunctionalTestCase
{
    protected const FIRST_DOMAIN_SUBSCRIBER_EMAIL = 'james.black@no-reply.com';

    /**
     * @inject
     */
    private NewsletterRepository $newsletterRepository;

    public function testSubscriberFoundInFirstDomain(): void
    {
        $iterator = $this->newsletterRepository->getAllEmailsDataIteratorByDomainId(Domain::FIRST_DOMAIN_ID);
        $this->assertContainsNewsletterSubscriber($iterator, self::FIRST_DOMAIN_SUBSCRIBER_EMAIL);
    }

    public function testSubscriberNotFoundInSecondDomain(): void
    {
        $iterator = $this->newsletterRepository->getAllEmailsDataIteratorByDomainId(Domain::SECOND_DOMAIN_ID);
        $this->assertNotContainsNewsletterSubscriber($iterator, self::FIRST_DOMAIN_SUBSCRIBER_EMAIL);
    }

    /**
     * @param iterable<array{email: string, createdAt: string}> $iterator
     */
    private function assertContainsNewsletterSubscriber(iterable $iterator, string $email): void
    {
        foreach ($iterator as $row) {
            if ($row['email'] === $email) {
                return;
            }
        }

        Assert::fail('Newsletter subscriber was not found, but was expected');
    }

    /**
     * @param iterable<array{email: string, createdAt: string}> $iterator
     */
    private function assertNotContainsNewsletterSubscriber(iterable $iterator, string $email): void
    {
        foreach ($iterator as $row) {
            if ($row['email'] === $email) {
                Assert::fail('Newsletter subscriber was found, but was not expected');
            }
        }
    }
}
