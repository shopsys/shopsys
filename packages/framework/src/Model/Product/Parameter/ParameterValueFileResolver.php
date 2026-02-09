<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataExtractor;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class ParameterValueFileResolver
{
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly UploadedFileDataExtractor $uploadedFileDataExtractor,
    ) {
    }

    public function addIconDataToParameterValuesData(
        array $parameterValuesData,
        DomainConfig $domainConfig,
    ): array {
        $parameterValueIds = array_column($parameterValuesData, 'parameter_value_id');
        $filesIndexedByParameterValueId = $this->uploadedFileFacade->getAllFilesIndexedByEntityId(
            $parameterValueIds,
            ParameterValue::ENTITY_NAME_FOR_FILES_CONFIG,
            null,
        );

        foreach ($parameterValuesData as $key => $parameterValueData) {
            $parameterValueFiles = $filesIndexedByParameterValueId[$parameterValueData['parameter_value_id']] ?? [];

            if ($parameterValueFiles === []) {
                $parameterValuesData[$key]['parameter_value_icon_anchor_text'] = null;
                $parameterValuesData[$key]['parameter_value_icon_url'] = null;

                continue;
            }

            $firstFile = reset($parameterValueFiles);
            $resolvedFileData = $this->uploadedFileDataExtractor->extractUploadedFileData($firstFile, $domainConfig);

            $parameterValuesData[$key]['parameter_value_icon_anchor_text'] = $resolvedFileData['anchorText'];
            $parameterValuesData[$key]['parameter_value_icon_url'] = $resolvedFileData['url'];
        }

        return $parameterValuesData;
    }
}
