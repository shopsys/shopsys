<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Psr\Container\ContainerInterface;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationMessageHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrameworkBundle\Test\ProductIndexBackupFacade;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;

abstract class WebTestCase extends BaseWebTestCase implements ServiceContainerTestCase
{
    /**
     * @inject
     */
    private PersistentReferenceFacade $persistentReferenceFacade;

    /**
     * @inject
     */
    protected Domain $domain;

    /**
     * @inject
     */
    protected CurrencyFacade $currencyFacade;

    /**
     * @inject
     */
    protected ProductRecalculationMessageHandler $productRecalculationMessageHandler;

    /**
     * @inject
     */
    protected ProductIndexBackupFacade $productIndexBackupFacade;

    /**
     * @inject
     */
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);
    }

    /**
     * @return \Psr\Container\ContainerInterface
     */
    abstract public function createContainer(): ContainerInterface;

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $server
     * @return \Tests\App\Test\Client
     */
    protected static function createClient(array $options = [], array $server = []): Client
    {
        /** @var \Tests\App\Test\Client $client */
        $client = parent::createClient($options, $server);

        return $client;
    }

    /**
     * @param string $referenceName
     * @return object
     */
    protected function getReference(string $referenceName): object
    {
        return $this->persistentReferenceFacade->getReference($referenceName);
    }

    /**
     * @param string $referenceName
     * @param int $domainId
     * @return object
     */
    protected function getReferenceForDomain(string $referenceName, int $domainId): object
    {
        return $this->persistentReferenceFacade->getReferenceForDomain($referenceName, $domainId);
    }

    /**
     * @param string $routeName
     * @param array<string, mixed> $parameters
     * @param int $pathType
     * @return string
     */
    protected function getLocalizedPathOnFirstDomainByRouteName(
        string $routeName,
        array $parameters = [],
        int $pathType = UrlGeneratorInterface::ABSOLUTE_URL,
    ): string {
        $domainRouterFactory = self::getContainer()->get(DomainRouterFactory::class);

        return $domainRouterFactory->getRouter(Domain::FIRST_DOMAIN_ID)
            ->generate($routeName, $parameters, $pathType);
    }

    protected function skipTestIfFirstDomainIsNotInEnglish(): void
    {
        if ($this->getFirstDomainLocale() !== 'en') {
            $this->markTestSkipped(
                'Tests for product searching are run only when the first domain has English locale',
            );
        }
    }

    /**
     * @return string
     */
    protected function getFirstDomainLocale(): string
    {
        return $this->domain->getLocale();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    protected function getFirstDomainCurrency(): Currency
    {
        return $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());
    }

    protected function tearDown(): void
    {
        $this->productIndexBackupFacade->restoreSnapshotIfPreviouslyCreated();

        parent::tearDown();
    }

    /**
     * Consumes messages dispatched by ProductRecalculationDispatcher class and run recalculations for dispatched messages
     */
    public function handleDispatchedRecalculationMessages(): void
    {
        $this->productIndexBackupFacade->createSnapshot();

        $this->dispatchFakeKernelResponseEventToTriggerSendMessageToTransport();

        /** @var \Symfony\Component\Messenger\Transport\InMemoryTransport $regularPriorityTransport */
        $regularPriorityTransport = self::getContainer()->get('messenger.transport.product_recalculation_priority_regular');
        /** @var \Symfony\Component\Messenger\Transport\InMemoryTransport $highPriorityTransport */
        $highPriorityTransport = self::getContainer()->get('messenger.transport.product_recalculation_priority_high');
        $handler = $this->productRecalculationMessageHandler;

        $envelopes = [...$highPriorityTransport->getSent(), ...$regularPriorityTransport->getSent()];

        foreach ($envelopes as $envelope) {
            $message = $envelope->getMessage();

            if ($message instanceof AbstractProductRecalculationMessage) {
                $handler($message);
            }
        }
    }

    /**
     * By dispatching the kernel response event, the message is sent to the transport thanks to the
     * DelayedEnvelope/DispatchCollectedEnvelopesSubscriber.
     * Until the subscriber is called, the messages are collected only in the DelayedEnvelopesCollector.
     */
    private function dispatchFakeKernelResponseEventToTriggerSendMessageToTransport(): void
    {
        $fakeKernelResponseEvent = new ResponseEvent(
            self::$kernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response(),
        );

        $this->eventDispatcher->dispatch($fakeKernelResponseEvent, 'kernel.response');
    }
}
