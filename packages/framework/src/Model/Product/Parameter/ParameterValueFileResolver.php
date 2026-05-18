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
                $parameterValuesData[$key]['parameter_value_icon_view_url'] = null;
                $parameterValuesData[$key]['parameter_value_icon_filesize'] = null;
                $parameterValuesData[$key]['parameter_value_icon_extension'] = null;

                continue;
            }

            $firstFile = array_first($parameterValueFiles);
            $resolvedFileData = $this->uploadedFileDataExtractor->extractUploadedFileData($firstFile, $domainConfig);

            $parameterValuesData[$key]['parameter_value_icon_anchor_text'] = $resolvedFileData['anchorText'];
            $parameterValuesData[$key]['parameter_value_icon_url'] = $resolvedFileData['url'];
            $parameterValuesData[$key]['parameter_value_icon_view_url'] = $resolvedFileData['viewUrl'];
            $parameterValuesData[$key]['parameter_value_icon_filesize'] = $resolvedFileData['filesize'];
            $parameterValuesData[$key]['parameter_value_icon_extension'] = $resolvedFileData['extension'];
        }

        return $parameterValuesData;
    }
}
