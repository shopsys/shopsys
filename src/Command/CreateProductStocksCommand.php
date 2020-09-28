<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use App\Model\Stock\ProductStockDataFactory;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\StockFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProductStocksCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'sconto:create-product-stocks';

    /**
     * @var ProductStockFacade
     */
    private ProductStockFacade $productStockFacade;

    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @var StockFacade
     */
    private StockFacade $stockFacade;

    /**
     * @var ProductStockDataFactory
     */
    private ProductStockDataFactory $productStockDataFactory;

    /**
     * @param ProductStockFacade $productStockFacade
     * @param ProductRepository $productRepository
     * @param StockFacade $stockFacade
     * @param ProductStockDataFactory $productStockDataFactory
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

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $stocks = $this->stockFacade->getAllStocks();

        /** @var Product $product */
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
    }
}
