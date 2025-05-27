<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Product;

use Shopsys\AiToolsBundle\Component\Ai\Application\AiVectorStoreFacade;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductVectorStoreFacade
{
    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Application\AiVectorStoreFacade $aiVectorStoreFacade
     */
    public function __construct(
        protected readonly AiVectorStoreFacade $aiVectorStoreFacade,
    ) {
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    public function exportProductToVectorStore(
        VectorStore $vectorStore,
        Product $product,
        DomainConfig $domainConfig,
    ): void {
        $payload = [];
        $payload['name'] = $product->getName($domainConfig->getLocale());
        $payload['description'] = $product->getDescription($domainConfig->getId());
        $payload['brand'] = $product->getBrand()->getName();
        $payload['categories'] = array_map(
            fn (Category $category) => $category->getName($domainConfig->getLocale()),
            $product->getCategoriesIndexedByDomainId()[$domainConfig->getId()],
        );
        $payload['catnum'] = $product->getCatnum();
        $payload['identifierKey'] = 'catnum';
        $payload['dataObject'] = 'product';

        //        d($payload);
        $this->aiVectorStoreFacade->appendObjectToVectorStore($vectorStore, $payload);
    }
}
