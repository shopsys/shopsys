<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Parameter;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFileResolver;

class ParameterValueIconsBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly Domain $domain,
        protected readonly ParameterValueFileResolver $parameterValueFileResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     */
    public function loadByParameterValues(array $parameterValues): Promise
    {
        $valuesWithIcons = $this->parameterValueFileResolver->addIconDataToParameterValuesData(
            array_map(static fn (ParameterValue $value) => [
                'parameter_value_id' => $value->getId(),
                'parameter_value_text' => $value->getText(),
            ], $parameterValues),
            $this->domain->getCurrentDomainConfig(),
        );

        return $this->promiseAdapter->all(array_map(static fn (array $value) => $value['parameter_value_icon_url'] === null ? null : [
            'anchorText' => $value['parameter_value_icon_anchor_text'],
            'url' => $value['parameter_value_icon_url'],
            'viewUrl' => $value['parameter_value_icon_view_url'],
            'filesize' => $value['parameter_value_icon_filesize'],
            'extension' => $value['parameter_value_icon_extension'],
        ], $valuesWithIcons));
    }
}
