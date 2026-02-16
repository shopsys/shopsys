<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\Admin\Country;

use Override;
use ReflectionClass;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Country\CountryFormType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\FormTypeLayout;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validation;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class CountryFormTypeTest extends TypeTestCase
{
    use SetTranslatorTrait;

    private Localization $localization;

    private Domain $domain;

    private CountryFacade $countryFacade;

    private UrlGeneratorInterface $urlGenerator;

    private FormTypeLayout $formTypeLayout;

    private function getFullCountryFormData(): array
    {
        return [
            'actionBar' => [
                'save' => '',
            ],
            'names' => [
                'en' => 'Czech republic',
                'cs' => 'Česká republika',
            ],
            'code' => 'CZ',
            'enabled' => [
                1 => '1',
                2 => '1',
            ],
            'priority' => [
                1 => '0',
                2 => '0',
            ],
        ];
    }

    public function testNameIsMandatory(): void
    {
        $countryFormData = $this->getFullCountryFormData();

        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryFormData);
        $this->assertTrue($countryForm->isValid(), 'Valid form');

        $countryFormData['names']['cs'] = '';

        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryFormData);
        $this->assertFalse($countryForm->isValid(), 'Invalid form - missing czech translation');
    }

    public function testPriorityIsNumber(): void
    {
        $countryFormData = $this->getFullCountryFormData();

        $countryFormData['priority'][1] = 'asd';

        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryFormData);
        $this->assertFalse($countryForm->isValid(), 'Invalid form');

        $countryFormData['priority'][1] = '1';

        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryFormData);
        $this->assertTrue($countryForm->isValid(), 'Valid form');
    }

    public function testCodeIsUnique(): void
    {
        $countryFormData = $this->getFullCountryFormData();

        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryFormData);
        $this->assertTrue($countryForm->isValid(), 'Non-existent country code');

        $countryFormData['code'] = 'UZ';
        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryFormData);
        $this->assertFalse($countryForm->isValid(), 'Existing country code');
    }

    public function testCodeIsNotDuplicateOnEdit(): void
    {
        $countryFormData = $this->getFullCountryFormData();
        $countryFormData['code'] = 'UZ';

        $country = $this->countryFacade->findByCode('UZ');

        $countryForm = $this->createCountryForm($country);
        $countryForm->submit($countryFormData);
        $this->assertTrue($countryForm->isValid(), 'Existing country code on edit');
    }

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);

        $this->setTranslator();

        $this->localization = $this->createStub(Localization::class);
        $this->localization->method('getLocalesOfAllDomains')->willReturn(['cs', 'en']);
        $this->localization->method('getAdminEnabledLocales')->willReturn(['cs', 'en']);
        $this->localization->method('getCurrentLocaleForTranslatableEntities')->willReturn('en');

        $this->domain = $this->createStub(Domain::class);

        $domainConfigs = [
            DomainConfigHelper::getDomainConfig(),
            DomainConfigHelper::getDomainConfig(
                id: Domain::SECOND_DOMAIN_ID,
                locale: 'en',
            ),
        ];

        $this->domain->method('getAll')->willReturn($domainConfigs);
        $this->domain->method('getAllIds')->willReturn([1, 2]);
        $this->domain->method('getAdminEnabledDomainIds')->willReturn([1, 2]);
        $this->domain->method('getAdminEnabledDomains')->willReturn($domainConfigs);

        $countryData = new CountryData();
        $countryData->code = 'UZ';
        $countryData->enabled = [1 => true, 2 => true];
        $countryData->names = ['cs' => 'Uzbekistán', 'en' => 'Uzbekistan'];

        $country = new Country($countryData);

        /* Entity returned by mock have to have Id properly set */
        $reflection = new ReflectionClass($country);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($country, 1);

        $this->countryFacade = $this->createStub(CountryFacade::class);
        $this->countryFacade->method('findByCode')->willReturnMap([['CZ', null], ['UZ', $country]]);
        $this->countryFacade->method('getAll')->willReturn([$country]);

        $this->urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $this->urlGenerator->method('generate')->willReturn(DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL);

        $this->formTypeLayout = new FormTypeLayout();

        parent::setUp();
    }

    #[Override]
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension(
                [
                    new CountryFormType($this->countryFacade),
                    new LocalizedType($this->localization, $this->formTypeLayout),
                    new DomainsType($this->domain),
                    new MultidomainType($this->domain, $this->formTypeLayout),
                    new ActionBarType($this->urlGenerator),
                ],
                [],
            ),
        ];
    }

    private function createCountryForm(?Country $country = null): FormInterface
    {
        return $this->factory->create(CountryFormType::class, null, ['country' => $country]);
    }
}
