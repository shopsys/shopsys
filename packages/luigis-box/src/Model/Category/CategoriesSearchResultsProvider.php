<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Category;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Override;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\Search\CategoriesSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadResult;
use Shopsys\LuigisBoxBundle\Model\Provider\SearchResultsProvider;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class CategoriesSearchResultsProvider extends SearchResultsProvider implements CategoriesSearchResultsProviderInterface
{
    public function __construct(
        string $enabledDomainIds,
        protected readonly DataLoaderInterface $luigisBoxBatchLoader,
        protected readonly LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory,
    ) {
        parent::__construct($enabledDomainIds);
    }

    #[Override]
    public function getCategoriesSearchResults(
        Argument $argument,
    ): Promise|ConnectionInterface {
        $batchLoadResult = null;

        $paginator = new Paginator(
            function ($offset, $limit) use ($argument, &$batchLoadResult) {
                $batchLoadData = $this->luigisBoxBatchLoadDataFactory->createForSearch(
                    TypeInLuigisBoxEnum::CATEGORY,
                    $limit,
                    $offset,
                    $argument,
                );

                return $this->luigisBoxBatchLoader->load($batchLoadData)
                    ->then(static function (LuigisBoxBatchLoadResult $result) use (&$batchLoadResult): array {
                        $batchLoadResult = $result;

                        return $result->getData();
                    });
            },
            Paginator::MODE_PROMISE,
        );

        /** @var \GraphQL\Executor\Promise\Promise $promise */
        $promise = $paginator->auto($argument, 0);

        $promise->then(function (ConnectionInterface $connection) use (&$batchLoadResult): void {
            $connection->setTotalCount(
                $batchLoadResult?->getTotalCount() ?? 0,
            );
        });

        return $promise;
    }
}
