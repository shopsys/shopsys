<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ParameterFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface $parameterFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly ParameterFactoryInterface $parameterFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param int $parameterId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getById($parameterId)
    {
        return $this->parameterRepository->getById($parameterId);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getByUuid(string $uuid): Parameter
    {
        return $this->parameterRepository->getByUuid($uuid);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueByUuid(string $uuid): ParameterValue
    {
        return $this->parameterRepository->getParameterValueByUuid($uuid);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getAll()
    {
        return $this->parameterRepository->getAll();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function create(ParameterData $parameterData)
    {
        $parameter = $this->parameterFactory->create($parameterData);
        $this->em->persist($parameter);
        $this->em->flush();

        $this->dispatchParameterEvent($parameter, ParameterEvent::CREATE);

        return $parameter;
    }

    /**
     * @param string[] $namesByLocale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter|null
     */
    public function findParameterByNames(array $namesByLocale)
    {
        return $this->parameterRepository->findParameterByNames($namesByLocale);
    }

    /**
     * @param int $parameterId
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function edit($parameterId, ParameterData $parameterData)
    {
        $parameter = $this->parameterRepository->getById($parameterId);
        $parameter->edit($parameterData);
        $this->em->flush();

        $this->dispatchParameterEvent($parameter, ParameterEvent::UPDATE);

        return $parameter;
    }

    /**
     * @param int $parameterId
     */
    public function deleteById($parameterId)
    {
        $parameter = $this->parameterRepository->getById($parameterId);

        $this->em->remove($parameter);

        $this->dispatchParameterEvent($parameter, ParameterEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @param string $valueText
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueByValueTextAndLocale(string $valueText, string $locale): ParameterValue
    {
        return $this->parameterRepository->getParameterValueByValueTextAndLocale($valueText, $locale);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param string $eventType
     * @see \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterEvent class
     */
    protected function dispatchParameterEvent(Parameter $parameter, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new ParameterEvent($parameter), $eventType);
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersByUuids(array $uuids): array
    {
        return $this->parameterRepository->getParametersByUuids($uuids);
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesByUuids(array $uuids): array
    {
        return $this->parameterRepository->getParameterValuesByUuids($uuids);
    }
}
