<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomain;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomainValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorInterface;

/**
 * The default factory can instantiate only validators without dependencies, this one adds the validator of UniqueSlugsOnDomain
 */
class UniqueSlugsOnDomainValidatorFactory extends ConstraintValidatorFactory
{
    public function __construct(
        private readonly Domain $domain,
        private readonly DomainRouterFactory $domainRouterFactory,
    ) {
        parent::__construct();
    }

    #[Override]
    public function getInstance(Constraint $constraint): ConstraintValidatorInterface
    {
        if ($constraint instanceof UniqueSlugsOnDomain) {
            return new UniqueSlugsOnDomainValidator($this->domain, $this->domainRouterFactory);
        }

        return parent::getInstance($constraint);
    }
}
