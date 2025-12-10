<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ParameterValueFilterOption
{
    protected ?UploadedFileFacade $uploadedFileFacade = null;

    protected ?Domain $domain = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     * @param int $count
     * @param bool $isAbsolute
     * @param bool $isSelected
     */
    public function __construct(
        public readonly ParameterValue $parameterValue,
        public readonly int $count,
        public readonly bool $isAbsolute,
        public readonly bool $isSelected,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function setUploadedFileDependencies(UploadedFileFacade $uploadedFileFacade, Domain $domain): void
    {
        $this->uploadedFileFacade = $uploadedFileFacade;
        $this->domain = $domain;
    }

    /**
     * @return array{url: string, name: string}|null
     */
    public function getColourIconImage(): ?array
    {
        if ($this->uploadedFileFacade === null || $this->domain === null) {
            return null;
        }

        $uploadedFiles = $this->uploadedFileFacade->getUploadedFilesByEntity($this->parameterValue);

        if (count($uploadedFiles) === 0) {
            return null;
        }

        $uploadedFile = $uploadedFiles[0];

        return [
            'url' => $this->uploadedFileFacade->getUploadedFileUrl($this->domain->getCurrentDomainConfig(), $uploadedFile),
            'name' => $uploadedFile->getName(),
        ];
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->parameterValue->getUuid();
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->parameterValue->getText();
    }

    /**
     * @return string|null
     */
    public function getNumericValue(): ?string
    {
        return $this->parameterValue->getNumericValue();
    }

    /**
     * @return string|null
     */
    public function getRgbHex(): ?string
    {
        return $this->parameterValue->getRgbHex();
    }
}
