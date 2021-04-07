<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Product\ProductRepository;
use App\Model\Stock\ProductStockDataFactory;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Command\CommandResultCodes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProductStocksCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'ssfwcc:create-product-stocks';

    /**
     * @var \App\Model\Stock\ProductStockFacade
     */
    private ProductStockFacade $productStockFacade;

    /**
     * @var \App\Model\Product\ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @var \App\Model\Stock\StockFacade
     */
    private StockFacade $stockFacade;

    /**
     * @var \App\Model\Stock\ProductStockDataFactory
     */
    private ProductStockDataFactory $productStockDataFactory;

    /**
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Stock\ProductStockDataFactory $productStockDataFactory
     */
    public function __construct(
        ProductStockFacade $productStockFacade,
        ProductRepository $productRepository,
        StockFacade $stockFacade,
        ProductStockDataFactory $productStockDataFactory
    ) {
        parent::__construct();

        $this->productStockFacade = $productStockFacade;
        $this->productRepository = $productRepository;
        $this->stockFacade = $stockFacade;
        $this->productStockDataFactory = $productStockDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Generate rows for each stock for each product if does not exist');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stocks = $this->stockFacade->getAllStocks();

        /** @var \App\Model\Product\Product $product */
        foreach ($this->productRepository->getAll() as $product) {
            $productCatnum = $product->getCatnum();
            $output->write(sprintf('Checking product %s stocks... ', $productCatnum));
            foreach ($stocks as $stock) {
                $externalStockId = $stock->getExternalId();
                $productStock = $this->productStockFacade->findProductStockByProductCatnumAndStockExternalId(
                    $productCatnum,
                    $externalStockId
                );
                if ($productStock === null) {
                    $this->productStockFacade->editProductStockRelation(
                        $product,
                        $stock,
                        $this->productStockDataFactory->createFromStock($stock)
                    );
                    $output->write(sprintf('%s created. ', $externalStockId));
                } else {
                    $output->write(sprintf('%s ok. ', $externalStockId));
                }
            }
            $output->writeln('');
        }

        return CommandResultCodes::RESULT_OK;
    }
}
