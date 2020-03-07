<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade as BaseParameterFacade;

/**
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\Parameter\ParameterRepository $parameterRepository, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface $parameterFactory, \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
 * @method \App\Model\Product\Parameter\Parameter getById(int $parameterId)
 * @method \App\Model\Product\Parameter\Parameter[] getAll()
 * @method \App\Model\Product\Parameter\Parameter create(\App\Model\Product\Parameter\ParameterData $parameterData)
 * @method \App\Model\Product\Parameter\Parameter|null findParameterByNames(string[] $namesByLocale)
 * @method \App\Model\Product\Parameter\Parameter edit(int $parameterId, \App\Model\Product\Parameter\ParameterData $parameterData)
 * @method \App\Model\Product\Parameter\ParameterValue getParameterValueByValueTextAndLocale(string $valueText, string $locale)
 * @method dispatchParameterEvent(\App\Model\Product\Parameter\Parameter $parameter, string $eventType)
 */
class ParameterFacade extends BaseParameterFacade
{
    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Parameter\Parameter|null
     */
    public function findParameterByAkeneoCode(string $akeneoCode): ?Parameter
    {
        return $this->parameterRepository->findParameterByAkeneoCode($akeneoCode);
    }

    /**
     * @param int[] $parameterValueIdsByParameterId
     * @return string[]
     */
    public function getParameterValueNamesIndexedByParameterNames(array $parameterValueIdsByParameterId): array
    {
        $parameterValueNamesIndexedByParameterNames = [];
        foreach ($parameterValueIdsByParameterId as $parameterId => $parameterValueId) {
            $parameter = $this->getById((int)$parameterId);
            $parameterValue = $this->parameterRepository->getParameterValueById((int)$parameterValueId);

            $parameterValueNamesIndexedByParameterNames[$parameter->getName()] = $parameterValue->getText();
        }

        return $parameterValueNamesIndexedByParameterNames;
    }

    /**
     * @param int $parameterValueId
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueById(int $parameterValueId): ParameterValue
    {
        return $this->parameterRepository->getParameterValueById($parameterValueId);
    }
}
