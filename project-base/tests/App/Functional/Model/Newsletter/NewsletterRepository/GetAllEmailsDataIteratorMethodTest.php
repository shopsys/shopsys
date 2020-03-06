<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Newsletter\NewsletterRepository;

use Doctrine\ORM\Internal\Hydration\IterableResult;
use PHPUnit\Framework\Assert;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class GetAllEmailsDataIteratorMethodTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    protected const FIRST_DOMAIN_SUBSCRIBER_EMAIL = 'james.black@no-reply.com';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterRepository
     * @inject
     */
    private $newsletterRepository;

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
     * @param \Doctrine\ORM\Internal\Hydration\IterableResult $iterator
     * @param string $email
     */
    private function assertContainsNewsletterSubscriber(IterableResult $iterator, string $email): void
    {
        foreach ($iterator as $row) {
            if ($row[0]['email'] === $email) {
                return;
            }
        }

        Assert::fail('Newsletter subscriber was not found, but was expected');
    }

    /**
     * @param \Doctrine\ORM\Internal\Hydration\IterableResult $iterator
     * @param string $email
     */
    private function assertNotContainsNewsletterSubscriber(IterableResult $iterator, string $email): void
    {
        foreach ($iterator as $row) {
            if ($row[0]['email'] === $email) {
                Assert::fail('Newsletter subscriber was found, but was not expected');
            }
        }
    }
}
