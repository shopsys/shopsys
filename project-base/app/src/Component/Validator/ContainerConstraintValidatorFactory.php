<?php

declare(strict_types=1);

namespace App\Component\Validator;

use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory as SymfonyContainerConstraintValidatorFactory;

class ContainerConstraintValidatorFactory extends SymfonyContainerConstraintValidatorFactory
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $appContainer;

    /**
     * @var string[]
     */
    private $validatorClassByServiceId;

    /**
     * @param \Psr\Container\ContainerInterface $validatorContainer
     * @param \Psr\Container\ContainerInterface $appContainer
     * @param string[] $validatorClassByServiceId
     */
    public function __construct(ContainerInterface $validatorContainer, ContainerInterface $appContainer, array $validatorClassByServiceId)
    {
        parent::__construct($validatorContainer);

        $this->appContainer = $appContainer;
        $this->validatorClassByServiceId = $validatorClassByServiceId;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance(Constraint $constraint)
    {
        $name = $constraint->validatedBy();

        if (array_key_exists($name, $this->validatorClassByServiceId)) {
            return $this->appContainer->get($this->validatorClassByServiceId[$name]);
        }

        return parent::getInstance($constraint);
    }
}
