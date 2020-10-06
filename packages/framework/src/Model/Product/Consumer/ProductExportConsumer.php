<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Consumer;

use Bunny\Message;
use Cdn77\RabbitMQBundle\RabbitMQ\Consumer\Configuration;
use Cdn77\RabbitMQBundle\RabbitMQ\Consumer\Consumer;
use Cdn77\RabbitMQBundle\RabbitMQ\Operation\AcknowledgeOperation;
use Cdn77\RabbitMQBundle\RabbitMQ\Operation\RejectOperation;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Throwable;

class ProductExportConsumer implements Consumer
{
    protected const QUEUE_NAME = 'products_export';

    /**
     * @var \Cdn77\RabbitMQBundle\RabbitMQ\Operation\AcknowledgeOperation
     */
    protected $acknowledgeOperation;

    /**
     * @var \Cdn77\RabbitMQBundle\RabbitMQ\Operation\RejectOperation
     */
    protected $rejectOperation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex
     */
    protected $productIndex;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade
     */
    protected $indexFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    protected $indexDefinitionLoader;

    /**
     * @param \Cdn77\RabbitMQBundle\RabbitMQ\Operation\AcknowledgeOperation $acknowledgeOperation
     * @param \Cdn77\RabbitMQBundle\RabbitMQ\Operation\RejectOperation $rejectOperation
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex $productIndex
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     */
    public function __construct(
        AcknowledgeOperation $acknowledgeOperation,
        RejectOperation $rejectOperation,
        ProductIndex $productIndex,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader
    ) {
        $this->acknowledgeOperation = $acknowledgeOperation;
        $this->rejectOperation = $rejectOperation;
        $this->productIndex = $productIndex;
        $this->indexFacade = $indexFacade;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
    }

    /**
     * @param \Bunny\Message $bunnyMessage
     */
    public function consume(Message $bunnyMessage): void
    {
        $productData = json_decode($bunnyMessage->content, true);

        try {
            // TODO change fix domain
            $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(
                $this->productIndex::getName(),
                Domain::FIRST_DOMAIN_ID
            );

            $this->indexFacade->exportIds($this->productIndex, $indexDefinition, [$productData['product_id']]);
        } catch (Throwable $throwable) {
            $this->rejectOperation->handle($bunnyMessage);
            return;
        }

        $this->acknowledgeOperation->handle($bunnyMessage);
    }

    /**
     * @return \Cdn77\RabbitMQBundle\RabbitMQ\Consumer\Configuration
     */
    public function getConfiguration(): Configuration
    {
        $prefetchCount = 1;
        $prefetchSize = 0;
        $maxMessages = 100;
        $maxSeconds = 1000;

        return new Configuration(self::QUEUE_NAME, $prefetchCount, $prefetchSize, $maxMessages, $maxSeconds);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'products_export_consumer';
    }
}
