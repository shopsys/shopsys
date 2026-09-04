<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\Admin\Seo;

use Override;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoAttributesFormType;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoGroupType;
use Shopsys\FrameworkBundle\Form\FormTypeLayout;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData;
use Shopsys\FrameworkBundle\Model\Seo\SeoMetaRobotsEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class SeoGroupTypeTest extends TypeTestCase
{
    use SetTranslatorTrait;

    private const string PLACEHOLDER_SOURCE_INPUT_ID = 'entity_form_name';

    private const array LOCALES_BY_DOMAIN_ID = [
        Domain::FIRST_DOMAIN_ID => 'cs',
        Domain::SECOND_DOMAIN_ID => 'en',
    ];

    private Domain $domain;

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);

        $this->setTranslator();

        $domainConfigsByDomainId = [];

        foreach (self::LOCALES_BY_DOMAIN_ID as $domainId => $locale) {
            $domainConfigsByDomainId[$domainId] = DomainConfigHelper::getDomainConfig(
                id: $domainId,
                url: self::getDomainUrl($domainId),
                locale: $locale,
            );
        }

        $this->domain = $this->createStub(Domain::class);
        $this->domain->method('getAdminEnabledDomainIds')->willReturn(array_keys(self::LOCALES_BY_DOMAIN_ID));
        $this->domain->method('getAdminEnabledDomains')->willReturn(array_values($domainConfigsByDomainId));
        $this->domain->method('getDomainConfigById')->willReturnCallback(
            static fn (int $domainId) => $domainConfigsByDomainId[$domainId],
        );

        parent::setUp();
    }

    public function testUrlsFieldIsNotAddedWhenUrlListOptionsAreNull(): void
    {
        $form = $this->createFormWithSeoGroup();

        $this->assertFalse($form->get('seoGroup')->has('urls'));
    }

    public function testPlaceholderSourceInputIdIsAppliedToTitleAndH1ForSingleDomain(): void
    {
        $form = $this->createFormWithSeoGroup([
            'domain_id' => Domain::FIRST_DOMAIN_ID,
            'placeholder_source_input_id' => self::PLACEHOLDER_SOURCE_INPUT_ID,
        ]);

        $seoAttributesForm = $form->get('seoGroup')->get('seo');

        foreach (['title', 'h1'] as $fieldName) {
            $attr = $seoAttributesForm->get($fieldName)->getConfig()->getOption('attr');

            $this->assertSame(self::PLACEHOLDER_SOURCE_INPUT_ID, $attr['data-js-placeholder-source-input-id']);
        }
    }

    public function testPlaceholderSourceInputIdWithTokensIsAppliedToTitleAndH1PerDomain(): void
    {
        $form = $this->createFormWithSeoGroup([
            'placeholder_source_input_id' => 'entity_form_{domain_id}_name_{locale}',
        ]);

        foreach (self::LOCALES_BY_DOMAIN_ID as $domainId => $locale) {
            $seoAttributesForm = $form->get('seoGroup')->get('seo')->get((string)$domainId);

            foreach (['title', 'h1'] as $fieldName) {
                $attr = $seoAttributesForm->get($fieldName)->getConfig()->getOption('attr');

                $this->assertSame(
                    sprintf('entity_form_%d_name_%s', $domainId, $locale),
                    $attr['data-js-placeholder-source-input-id'],
                );
            }
        }
    }

    public function testH1RequiredMakesH1MandatoryOnAllDomains(): void
    {
        $form = $this->createFormWithSeoGroup([
            'h1_required' => true,
        ]);

        foreach (array_keys(self::LOCALES_BY_DOMAIN_ID) as $domainId) {
            $h1Config = $form->get('seoGroup')->get('seo')->get((string)$domainId)->get('h1')->getConfig();

            $this->assertTrue($h1Config->getOption('required'));
            $this->assertInstanceOf(NotBlank::class, $h1Config->getOption('constraints')[0]);
        }
    }

    private static function getDomainUrl(int $domainId): string
    {
        return sprintf('https://example-%d.com', $domainId);
    }

    #[Override]
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension(
                [
                    new SeoGroupType($this->domain),
                    new SeoAttributesFormType(new SeoMetaRobotsEnum(), $this->domain),
                    new MultidomainType($this->domain, new FormTypeLayout()),
                ],
                [],
            ),
        ];
    }

    /**
     * @param array<string, mixed> $seoGroupOptions
     */
    private function createFormWithSeoGroup(array $seoGroupOptions = []): FormInterface
    {
        if (($seoGroupOptions['domain_id'] ?? null) === null) {
            $seoData = [];

            foreach (array_keys(self::LOCALES_BY_DOMAIN_ID) as $domainId) {
                $seoData[$domainId] = new SeoAttributesData();
            }
        } else {
            $seoData = new SeoAttributesData();
        }

        return $this->factory->createBuilder(FormType::class, ['seo' => $seoData])
            ->add('seoGroup', SeoGroupType::class, $seoGroupOptions)
            ->getForm();
    }
}
