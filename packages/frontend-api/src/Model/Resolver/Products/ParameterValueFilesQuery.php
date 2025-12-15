<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ParameterValueFilesQuery extends AbstractQuery
{
    public const string PARAMETER_VALUE_ENTITY_NAME = 'parameterValue';

    /**
     * @param \Overblog\DataLoader\DataLoaderInterface $filesBatchLoader
     */
    public function __construct(
        protected readonly DataLoaderInterface $filesBatchLoader,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption $parameterValueFilterOption
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function colorIconByParameterValueFilterOptionPromiseQuery(
        ParameterValueFilterOption $parameterValueFilterOption,
    ): Promise {
        return $this->filesBatchLoader->load(
            new FileBatchLoadData(
                $parameterValueFilterOption->parameterValue->getId(),
                static::PARAMETER_VALUE_ENTITY_NAME,
                UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
            ),
        );
    }
}
