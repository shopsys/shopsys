<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Parameter;

use App\Model\Product\Parameter\Exception\ParameterValueNotFoundException;
use Doctrine\ORM\NoResultException;

class ParameterFacade
{
    /**
     * @var \App\FrontendApi\Model\Parameter\ParameterRepository
     */
    private ParameterRepository $parameterRepository;

    /**
     * @param \App\FrontendApi\Model\Parameter\ParameterRepository $parameterRepository
     */
    public function __construct(ParameterRepository $parameterRepository)
    {
        $this->parameterRepository = $parameterRepository;
    }

    /**
     * @param string[] $parameterUuids
     * @return array<string, int>
     */
    public function getParameterIdsIndexedByUuids(array $parameterUuids): array
    {
        return $this->parameterRepository->getParameterIdsIndexedByUuids($parameterUuids);
    }

    /**
     * @param string[] $parameterValueUuids
     * @return array<string, int>
     */
    public function getParameterValueIdsIndexedByUuids(array $parameterValueUuids): array
    {
        return $this->parameterRepository->getParameterValueIdsIndexedByUuids($parameterValueUuids);
    }

    /**
     * @param string $text
     * @param string $locale
     * @return int
     */
    public function getParameterValueIdByText(string $text, string $locale): int
    {
        try {
            return $this->parameterRepository->getParameterValueIdByText($text, $locale);
        } catch (NoResultException $noResultException) {
            throw new ParameterValueNotFoundException();
        }
    }
}
