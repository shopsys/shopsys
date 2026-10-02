<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomain;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomainValidator;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class UniqueSlugsOnDomainValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function createValidator(): ConstraintValidatorInterface
    {
        $domainConfigs = [
            DomainConfigHelper::getDomainConfig(
                url: 'http://example.cz',
                name: 'name1',
                baseUrl: 'http://example.cz',
            ),
            DomainConfigHelper::getDomainConfig(
                id: Domain::SECOND_DOMAIN_ID,
                name: 'name2',
                locale: 'en',
            ),
        ];
        $settingStub = $this->createStub(Setting::class);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            $domainConfigs,
            $settingStub,
            $currentAdministratorStub,
        );

        $routerStub = $this->createStub(DomainRouter::class);
        $routerStub->method('match')->willReturnCallback(function ($path) {
            if ($path !== '/existing-url/') {
                throw new ResourceNotFoundException();
            }

            return [];
        });

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);
        $domainRouterFactoryStub->method('getRouter')->willReturn($routerStub);

        return new UniqueSlugsOnDomainValidator($domain, $domainRouterFactoryStub);
    }

    public function testValidateSameSlugsOnDifferentDomains(): void
    {
        $newSlugs = [
            'new-url/',
        ];

        $this->validator->validate($newSlugs, new UniqueSlugsOnDomain(domainId: Domain::FIRST_DOMAIN_ID));
        $this->validator->validate($newSlugs, new UniqueSlugsOnDomain(domainId: Domain::SECOND_DOMAIN_ID));

        $this->assertNoViolation();
    }

    public function testValidateDuplicateSlugsOnSameDomain(): void
    {
        $newSlugs = [
            'new-url/',
            'new-url/',
        ];
        $constraint = new UniqueSlugsOnDomain(domainId: Domain::FIRST_DOMAIN_ID, messageDuplicate: 'myMessage');

        $this->validator->validate($newSlugs, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ url }}', 'http://example.cz/new-url/')
            ->assertRaised();
    }

    public function testValidateDuplicateEncodedAndDecodedSlugsOnSameDomain(): void
    {
        $newSlugs = [
            'new-%75rl/',
            'new-url/',
        ];
        $constraint = new UniqueSlugsOnDomain(domainId: Domain::FIRST_DOMAIN_ID, messageDuplicate: 'myMessage');

        $this->validator->validate($newSlugs, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ url }}', 'http://example.cz/new-url/')
            ->assertRaised();
    }

    public function testValidateDuplicateSlugsWithDifferentEncodingCaseOnSameDomain(): void
    {
        $newSlugs = [
            'new-caf%C3%A9/',
            'new-caf%c3%a9/',
        ];
        $constraint = new UniqueSlugsOnDomain(domainId: Domain::FIRST_DOMAIN_ID, messageDuplicate: 'myMessage');

        $this->validator->validate($newSlugs, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ url }}', 'http://example.cz/new-caf%C3%A9/')
            ->assertRaised();
    }

    public function testValidateExistingSlug(): void
    {
        $newSlugs = [
            'existing-url/',
        ];
        $constraint = new UniqueSlugsOnDomain(domainId: Domain::FIRST_DOMAIN_ID, message: 'myMessage');

        $this->validator->validate($newSlugs, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ url }}', 'http://example.cz/existing-url/')
            ->assertRaised();
    }

    public function testValidateExistingEncodedSlug(): void
    {
        $newSlugs = [
            'existing-%75rl/',
        ];
        $constraint = new UniqueSlugsOnDomain(domainId: Domain::FIRST_DOMAIN_ID, message: 'myMessage');

        $this->validator->validate($newSlugs, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ url }}', 'http://example.cz/existing-url/')
            ->assertRaised();
    }
}
