<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Product\ProductRepository;
use App\Model\Store\ProductStoreDataFactory;
use App\Model\Store\ProductStoreFacade;
use App\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Command\CommandResultCodes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProductStoresCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'ssfwcc:create-product-stores';

    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \App\Model\Product\ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @var \App\Model\Store\ProductStoreFacade
     */
    private ProductStoreFacade $productStoreFacade;

    /**
     * @var \App\Model\Store\ProductStoreDataFactory
     */
    private ProductStoreDataFactory $productStoreDataFactory;

    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Model\Store\ProductStoreFacade $productStoreFacade
     * @param \App\Model\Store\ProductStoreDataFactory $productStoreDataFactory
     */
    public function __construct(
        StoreFacade $storeFacade,
        ProductRepository $productRepository,
        ProductStoreFacade $productStoreFacade,
        ProductStoreDataFactory $productStoreDataFactory
    ) {
        parent::__construct();

        $this->storeFacade = $storeFacade;
        $this->productRepository = $productRepository;
        $this->productStoreFacade = $productStoreFacade;
        $this->productStoreDataFactory = $productStoreDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Generate rows for each store for each product if does not exist');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $stores = $this->storeFacade->getAllStores();

        foreach ($this->productRepository->getAll() as $product) {
            $productCatnum = $product->getCatnum();
            $output->write(sprintf('Checking product %s stores... ', $productCatnum));

            foreach ($stores as $store) {
                $storeId = $store->getId();
                $productStore = $this->productStoreFacade->findProductStoreByProductCatnumAndStoreId(
                    $productCatnum,
                    $storeId
                );

                if ($productStore === null) {
                    $this->productStoreFacade->editProductStoreRelation(
                        $product,
                        $store,
                        $this->productStoreDataFactory->createFromStore($store)
                    );

                    $this->productRepository->markProductsForExport([$product]);

                    $output->write(sprintf('%s created. ', $storeId));
                } else {
                    $output->write(sprintf('%s ok. ', $storeId));
                }
            }
            $output->writeln('');
        }

        return CommandResultCodes::RESULT_OK;
    }
}
