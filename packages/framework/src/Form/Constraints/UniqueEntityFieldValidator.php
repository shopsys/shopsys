<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEntityFieldValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntityField) {
            throw new UnexpectedTypeException($constraint, UniqueEntityField::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        $entity = $this->em->getRepository($constraint->entityName)->findOneBy([$constraint->fieldName => $value]);

        if ($entity !== null && $entity !== $constraint->entityInstance) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setParameter('{{ fieldName }}', $constraint->fieldName)
                ->addViolation();
        }
    }
}
