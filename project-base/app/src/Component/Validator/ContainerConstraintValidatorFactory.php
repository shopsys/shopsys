<?php

declare(strict_types=1);

namespace App\Component\Validator;

use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory as SymfonyContainerConstraintValidatorFactory;

class ContainerConstraintValidatorFactory extends SymfonyContainerConstraintValidatorFactory
{
    /**
     * @param \Psr\Container\ContainerInterface $validatorContainer
     * @param \Psr\Container\ContainerInterface $appContainer
     * @param string[] $validatorClassByServiceId
     */
    public function __construct(
        ContainerInterface $validatorContainer,
        private ContainerInterface $appContainer,
        private array $validatorClassByServiceId,
    ) {
        parent::__construct($validatorContainer);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance(Constraint $constraint): \Symfony\Component\Validator\ConstraintValidatorInterface
    {
        $name = $constraint->validatedBy();

        if (array_key_exists($name, $this->validatorClassByServiceId)) {
            return $this->appContainer->get($this->validatorClassByServiceId[$name]);
        }

        return parent::getInstance($constraint);
    }
}
