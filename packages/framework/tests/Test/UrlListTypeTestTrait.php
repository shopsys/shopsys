<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Validation;

/**
 * Wiring of UrlListType for form type unit tests — the type, its validator and a router that matches no route,
 * so that every new slug is free
 */
trait UrlListTypeTestTrait
{
    private function createDomainRouterFactoryMatchingNoRoute(): DomainRouterFactory
    {
        $routerStub = $this->createStub(DomainRouter::class);
        $routerStub->method('match')->willReturnCallback(static function (): never {
            throw new ResourceNotFoundException();
        });

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);
        $domainRouterFactoryStub->method('getRouter')->willReturn($routerStub);

        return $domainRouterFactoryStub;
    }

    private function createUrlListType(Domain $domain, DomainRouterFactory $domainRouterFactory): UrlListType
    {
        return new UrlListType($this->createStub(FriendlyUrlFacade::class), $domainRouterFactory, $domain);
    }

    private function createValidatorExtensionWithUniqueSlugsOnDomain(
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
    ): ValidatorExtension {
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new UniqueSlugsOnDomainValidatorFactory($domain, $domainRouterFactory))
            ->getValidator();

        return new ValidatorExtension($validator);
    }
}
