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
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoader;
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
        $paginator = new Paginator(
            function ($offset, $limit) use ($argument) {
                return $this->luigisBoxBatchLoader->load(
                    $this->luigisBoxBatchLoadDataFactory->createForSearch(
                        TypeInLuigisBoxEnum::CATEGORY,
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

        $promise->then(function (ConnectionInterface $connection): void {
            $connection->setTotalCount(LuigisBoxBatchLoader::getTotalByType(TypeInLuigisBoxEnum::CATEGORY));
        });

        return $promise;
    }
}
