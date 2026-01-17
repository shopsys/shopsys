<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData;
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
        return $this->filesBatchLoader->load(
            new FileBatchLoadData(
                $parameterValueFilterOption->parameterValue->getId(),
                ParameterValue::ENTITY_NAME_FOR_FILES_CONFIG,
                UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
            ),
        );
    }
}
