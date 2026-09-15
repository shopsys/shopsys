<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ParameterValueFilesQuery extends AbstractQuery
{
    public function __construct(
        protected readonly DataLoaderInterface $filesBatchLoader,
    ) {
    }

    public function colorIconByParameterValueFilterOptionPromiseQuery(
        ParameterValueFilterOption $parameterValueFilterOption,
    ): Promise {
        return $this->filesBatchLoader->load($parameterValueFilterOption->parameterValue);
    }
}
