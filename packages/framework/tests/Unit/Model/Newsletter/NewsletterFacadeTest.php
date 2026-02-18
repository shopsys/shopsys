<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Newsletter;

use Doctrine\ORM\EntityManager;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterRepository;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriberFactory;

class NewsletterFacadeTest extends TestCase
{
    private EntityManager|MockObject $em;

    private NewsletterRepository|Stub $newsletterRepository;

    private NewsletterFacade $newsletterFacade;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = $this->createMock(EntityManager::class);
        $this->newsletterRepository = $this->createStub(NewsletterRepository::class);
        $clock = $this->createStub(ClockInterface::class);

        $this->newsletterFacade = new NewsletterFacade(
            $this->em,
            $this->newsletterRepository,
            new NewsletterSubscriberFactory(new EntityNameResolver([])),
            $clock,
        );
    }

    public function testAddSubscribedEmail(): void
    {
        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(NewsletterSubscriber::class));

        $this->em
            ->expects($this->once())
            ->method('flush');

        $this->newsletterFacade->addSubscribedEmailIfNotExists('no-reply@shopsys.com', 1);
    }

    public function testDeleteSubscribedEmail(): void
    {
        $newsletterSubscriberInstance = $this->createStub(NewsletterSubscriber::class);

        $this->newsletterRepository
            ->method('findNewsletterSubscriberById')
            ->willReturn($newsletterSubscriberInstance);

        $this->em
            ->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(NewsletterSubscriber::class));

        $this->em
            ->expects($this->once())
            ->method('flush');

        $this->newsletterFacade->delete($newsletterSubscriberInstance);
    }
}
