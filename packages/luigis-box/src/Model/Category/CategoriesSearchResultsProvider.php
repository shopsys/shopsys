<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Category;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\Search\CategoriesSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoader;
use Shopsys\LuigisBoxBundle\Model\Provider\SearchResultsProvider;

class CategoriesSearchResultsProvider extends SearchResultsProvider implements CategoriesSearchResultsProviderInterface
{
    /**
     * @param string $enabledDomainIds
     * @param \Overblog\DataLoader\DataLoaderInterface $luigisBoxBatchLoader
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory
     */
    public function __construct(
        string $enabledDomainIds,
        protected readonly DataLoaderInterface $luigisBoxBatchLoader,
        protected readonly LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory,
    ) {
        parent::__construct($enabledDomainIds);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    public function getCategoriesSearchResults(
        Argument $argument,
    ): Promise|ConnectionInterface {
        $paginator = new Paginator(
            function ($offset, $limit) use ($argument) {
                return $this->luigisBoxBatchLoader->load(
                    $this->luigisBoxBatchLoadDataFactory->create(
                        LuigisBoxClient::TYPE_IN_LUIGIS_BOX_CATEGORY,
                        $limit,
                        $offset,
                        $argument,
                    ),
                );
            },
            Paginator::MODE_PROMISE,
        );

        /** @var \GraphQL\Executor\Promise\Promise $promise */
        $promise = $paginator->auto($argument, 0);

        $promise->then(function (ConnectionInterface $connection) {
            $connection->setTotalCount(LuigisBoxBatchLoader::getTotalByType(LuigisBoxClient::TYPE_IN_LUIGIS_BOX_CATEGORY));
        });

        return $promise;
    }
}
