<?php

declare(strict_types=1);

namespace Tests\App\Test;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\DispatchAllProductsMessage;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\DispatchAllProductsMessageHandler;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationMessageHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrameworkBundle\Test\ProductIndexBackupFacade;
use Zalas\Injector\Factory\DefaultExtractorFactory;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;
use Zalas\Injector\PHPUnit\TestListener\TestCaseContainerFactory;
use Zalas\Injector\Service\Injector;

abstract class WebTestCase extends BaseWebTestCase implements ServiceContainerTestCase
{
    /**
     * @inject
     */
    protected PersistentReferenceFacade $persistentReferenceFacade;

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
    protected DispatchAllProductsMessageHandler $dispatchAllProductsMessageHandler;

    /**
     * @inject
     */
    protected ProductIndexBackupFacade $productIndexBackupFacade;

    /**
     * @inject
     */
    protected EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->injectServices();

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
     * @template T
     * @param string $referenceName
     * @param class-string<T>|null $entityClassName
     * @return T
     */
    protected function getReference(string $referenceName, ?string $entityClassName = null)
    {
        return $this->persistentReferenceFacade->getReference($referenceName, $entityClassName);
    }

    /**
     * @template T
     * @param string $referenceName
     * @param int $domainId
     * @param class-string<T>|null $entityClassName
     * @return T
     */
    protected function getReferenceForDomain(
        string $referenceName,
        int $domainId,
        ?string $entityClassName = null,
    ) {
        return $this->persistentReferenceFacade->getReferenceForDomain($referenceName, $domainId, $entityClassName);
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
        // workaround for https://github.com/jakzal/phpunit-injector/issues/33
        if (!isset($this->productIndexBackupFacade)) {
            self::bootKernel();
            self::$kernel->getContainer()->get('test.service_container')->get(ProductIndexBackupFacade::class)->restoreSnapshotIfPreviouslyCreated();
        } else {
            $this->productIndexBackupFacade->restoreSnapshotIfPreviouslyCreated();
        }

        parent::tearDown();
    }

    /**
     * Consumes messages dispatched by ProductRecalculationDispatcher class and run recalculations for dispatched messages
     *
     * @param array|null $allowedProductIds If null, all messages are processed. Otherwise, only messages with product IDs in this array are processed.
     */
    public function handleDispatchedRecalculationMessages(?array $allowedProductIds = null): void
    {
        $this->productIndexBackupFacade->createSnapshot();

        $this->dispatchFakeKernelResponseEventToTriggerSendMessageToTransport();

        /** @var \Symfony\Component\Messenger\Transport\InMemoryTransport $regularPriorityTransport */
        $regularPriorityTransport = self::getContainer()->get('messenger.transport.product_recalculation_priority_regular');
        /** @var \Symfony\Component\Messenger\Transport\InMemoryTransport $highPriorityTransport */
        $highPriorityTransport = self::getContainer()->get('messenger.transport.product_recalculation_priority_high');
        $productRecalculationMessageHandler = $this->productRecalculationMessageHandler;
        $dispatchAllProductsMessageHandler = $this->dispatchAllProductsMessageHandler;

        $envelopes = $regularPriorityTransport->getSent();

        foreach ($envelopes as $envelope) {
            $message = $envelope->getMessage();

            if ($message instanceof DispatchAllProductsMessage) {
                $dispatchAllProductsMessageHandler($message);

                // all products were dispatched, so we can stop processing messages
                break;
            }
        }

        $this->dispatchFakeKernelResponseEventToTriggerSendMessageToTransport();
        $envelopes = [...$highPriorityTransport->getSent(), ...$regularPriorityTransport->getSent()];

        foreach ($envelopes as $envelope) {
            $message = $envelope->getMessage();

            if (!($message instanceof AbstractProductRecalculationMessage)) {
                continue;
            }

            if ($allowedProductIds !== null && !in_array($message->productId, $allowedProductIds, true)) {
                continue;
            }

            $productRecalculationMessageHandler($message);
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

    /**
     * workaround for https://github.com/jakzal/phpunit-injector/issues/33
     */
    protected function injectServices(): void
    {
        $injector = new Injector(new TestCaseContainerFactory($this), new DefaultExtractorFactory([TestCase::class, Assert::class]));
        $injector->inject($this);
    }
}
