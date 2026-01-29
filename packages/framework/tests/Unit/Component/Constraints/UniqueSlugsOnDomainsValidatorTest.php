<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomains;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomainsValidator;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class UniqueSlugsOnDomainsValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function createValidator()
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
        $settingMock = $this->createMock(Setting::class);
        $currentAdministratorMock = $this->createMock(CurrentAdministrator::class);

        $domain = new Domain(
            $domainConfigs,
            $settingMock,
            $currentAdministratorMock,
        );

        $routerMock = $this->getMockBuilder(DomainRouter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routerMock->method('match')->willReturnCallback(function ($path) {
            if ($path !== '/existing-url/') {
                throw new ResourceNotFoundException();
            }

            return [];
        });

        $domainRouterFactoryMock = $this->getMockBuilder(DomainRouterFactory::class)
            ->onlyMethods(['getRouter'])
            ->disableOriginalConstructor()
            ->getMock();
        $domainRouterFactoryMock->method('getRouter')->willReturn($routerMock);

        return new UniqueSlugsOnDomainsValidator($domain, $domainRouterFactoryMock);
    }

    public function testValidateSameSlugsOnDifferentDomains(): void
    {
        $values = [
            [
                UrlListData::FIELD_DOMAIN => 1,
                UrlListData::FIELD_SLUG => 'new-url/',
            ],
            [
                UrlListData::FIELD_DOMAIN => 2,
                UrlListData::FIELD_SLUG => 'new-url/',
            ],
        ];
        $constraint = new UniqueSlugsOnDomains();

        $this->validator->validate($values, $constraint);
        $this->assertNoViolation();
    }

    public function testValidateDuplicateSlugsOnSameDomain(): void
    {
        $values = [
            [
                UrlListData::FIELD_DOMAIN => 1,
                UrlListData::FIELD_SLUG => 'new-url/',
            ],
            [
                UrlListData::FIELD_DOMAIN => 1,
                UrlListData::FIELD_SLUG => 'new-url/',
            ],
        ];
        $constraint = new UniqueSlugsOnDomains();
        $constraint->messageDuplicate = 'myMessage';

        $this->validator->validate($values, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ url }}', 'http://example.cz/new-url/')
            ->assertRaised();
    }

    public function testValidateExistingSlug(): void
    {
        $values = [
            [
                UrlListData::FIELD_DOMAIN => 1,
                UrlListData::FIELD_SLUG => 'existing-url/',
            ],
        ];
        $constraint = new UniqueSlugsOnDomains();
        $constraint->message = 'myMessage';

        $this->validator->validate($values, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ url }}', 'http://example.cz/existing-url/')
            ->assertRaised();
    }
}
