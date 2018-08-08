<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;

class ParameterFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository
     */
    protected $parameterRepository;

    /**
     * @var ParameterFactoryInterface
     */
    protected $parameterFactory;

    public function __construct(
        EntityManagerInterface $em,
        ParameterRepository $parameterRepository,
        ParameterFactoryInterface $parameterFactory
    ) {
        $this->em = $em;
        $this->parameterRepository = $parameterRepository;
        $this->parameterFactory = $parameterFactory;
    }

    public function getById(int $parameterId): \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
    {
        return $this->parameterRepository->getById($parameterId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getAll(): array
    {
        return $this->parameterRepository->getAll();
    }

    public function create(ParameterData $parameterData): \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
    {
        $parameter = $this->parameterFactory->create($parameterData);
        $this->em->persist($parameter);
        $this->em->flush($parameter);

        return $parameter;
    }

    /**
     * @param string[] $namesByLocale
     */
    public function findParameterByNames(array $namesByLocale): ?\Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
    {
        return $this->parameterRepository->findParameterByNames($namesByLocale);
    }

    public function edit(int $parameterId, ParameterData $parameterData): \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
    {
        $parameter = $this->parameterRepository->getById($parameterId);
        $parameter->edit($parameterData);
        $this->em->flush();

        return $parameter;
    }

    public function deleteById(int $parameterId): void
    {
        $parameter = $this->parameterRepository->getById($parameterId);

        $this->em->remove($parameter);
        $this->em->flush();
    }
}
