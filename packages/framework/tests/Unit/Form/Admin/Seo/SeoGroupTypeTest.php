<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\Admin\Seo;

use Override;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoAttributesType;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoGroupType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomain;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomainValidator;
use Shopsys\FrameworkBundle\Form\FormTypeLayout;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandData;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData;
use Shopsys\FrameworkBundle\Model\Seo\SeoMetaRobotsEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Exception\OutOfBoundsException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Validation;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class SeoGroupTypeTest extends TypeTestCase
{
    use SetTranslatorTrait;

    private const string ROOT_FORM_NAME = 'entity_form';

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
                url: sprintf('https://example-%d.com', $domainId),
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
        $form = $this->createMultidomainForm();

        foreach (array_keys(self::LOCALES_BY_DOMAIN_ID) as $domainId) {
            $this->assertFalse($this->getDomainSeoForm($form, $domainId)->has('urls'));
        }
    }

    public function testSeoAttributesAreMappedPerDomainOfMultidomainEntity(): void
    {
        $brandData = new BrandData();

        foreach (array_keys(self::LOCALES_BY_DOMAIN_ID) as $domainId) {
            $brandData->seo[$domainId] = new SeoAttributesData();
        }

        $form = $this->createMultidomainForm(brandData: $brandData);

        foreach (array_keys(self::LOCALES_BY_DOMAIN_ID) as $domainId) {
            $this->assertSame($brandData->seo[$domainId], $this->getDomainSeoForm($form, $domainId)->get('seo')->getData());
        }
    }

    public function testSubmittedDataAreWrittenPerDomainOfMultidomainEntity(): void
    {
        $brandData = new BrandData();
        $brandData->seo[Domain::FIRST_DOMAIN_ID] = new SeoAttributesData();
        $brandData->seo[Domain::SECOND_DOMAIN_ID] = new SeoAttributesData();

        $form = $this->createMultidomainForm(['url_list_options' => ['route_name' => 'front_brand_detail']], $brandData);
        $form->submit([
            'seoGroup' => [
                'domains' => [
                    (string)Domain::SECOND_DOMAIN_ID => [
                        'seo' => ['title' => 'Second domain title'],
                        'urls' => ['newUrls' => [['slug' => 'second-domain-slug']]],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($form->isValid(), (string)$form->getErrors(true));
        $this->assertNull($brandData->seo[Domain::FIRST_DOMAIN_ID]->title);
        $this->assertSame('Second domain title', $brandData->seo[Domain::SECOND_DOMAIN_ID]->title);
        $this->assertSame('second-domain-slug', $brandData->urls[Domain::SECOND_DOMAIN_ID]->newUrls[0][UrlListData::FIELD_SLUG]);
        $this->assertSame([], $brandData->urls[Domain::FIRST_DOMAIN_ID]->newUrls);
    }

    public function testSubmittedDataAreWrittenForSingleDomainEntity(): void
    {
        $articleData = new ArticleData();
        $articleData->seo = new SeoAttributesData();

        $form = $this->createSingleDomainForm($articleData, ['url_list_options' => ['route_name' => 'front_article_detail']]);
        $form->submit([
            'seoGroup' => [
                'domain' => [
                    'seo' => ['title' => 'Article title'],
                    'urls' => ['newUrls' => [['slug' => 'article-slug']]],
                ],
            ],
        ]);

        $this->assertTrue($form->isValid(), (string)$form->getErrors(true));
        $this->assertSame('Article title', $articleData->seo->title);
        $this->assertSame('article-slug', $articleData->urls->newUrls[0][UrlListData::FIELD_SLUG]);
    }

    public function testSeoAttributesAreMappedForSingleDomainEntity(): void
    {
        $articleData = new ArticleData();
        $articleData->seo = new SeoAttributesData();

        $form = $this->createSingleDomainForm($articleData);

        $this->assertSame($articleData->seo, $form->get('seoGroup')->get('domain')->get('seo')->getData());
    }

    public function testPlaceholderSourceInputIdIsAppliedToTitleAndH1ForSingleDomain(): void
    {
        $articleData = new ArticleData();
        $articleData->seo = new SeoAttributesData();

        $view = $this->createSingleDomainForm($articleData, ['placeholder_source_path' => ['name']])->createView();

        foreach (['title', 'h1'] as $fieldName) {
            $attr = $view['seoGroup']['domain']['seo'][$fieldName]->vars['attr'];

            $this->assertSame(self::ROOT_FORM_NAME . '_name', $attr['data-js-placeholder-source-input-id']);
        }
    }

    public function testPlaceholderSourceInputIdWithTokensIsAppliedToTitleAndH1PerDomain(): void
    {
        $view = $this->createMultidomainForm(['placeholder_source_path' => ['names', '{locale}']])->createView();

        foreach (self::LOCALES_BY_DOMAIN_ID as $domainId => $locale) {
            foreach (['title', 'h1'] as $fieldName) {
                $attr = $view['seoGroup']['domains'][(string)$domainId]['seo'][$fieldName]->vars['attr'];

                $this->assertSame(
                    sprintf('%s_names_%s', self::ROOT_FORM_NAME, $locale),
                    $attr['data-js-placeholder-source-input-id'],
                );
            }
        }
    }

    public function testUnknownPlaceholderSourcePathFailsFast(): void
    {
        $form = $this->createMultidomainForm(['placeholder_source_path' => ['basicInformation', 'name']]);

        $this->expectException(OutOfBoundsException::class);

        $form->createView();
    }

    public function testH1RequiredMakesH1MandatoryOnAllDomains(): void
    {
        $form = $this->createMultidomainForm(['h1_required' => true]);

        foreach (array_keys(self::LOCALES_BY_DOMAIN_ID) as $domainId) {
            $h1Config = $this->getDomainSeoForm($form, $domainId)->get('seo')->get('h1')->getConfig();

            $this->assertTrue($h1Config->getOption('required'));
            $this->assertInstanceOf(NotBlank::class, $h1Config->getOption('constraints')[0]);
        }
    }

    #[Override]
    protected function getExtensions(): array
    {
        $routerStub = $this->createStub(DomainRouter::class);
        $routerStub->method('match')->willReturnCallback(static function (): never {
            throw new ResourceNotFoundException();
        });
        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);
        $domainRouterFactoryStub->method('getRouter')->willReturn($routerStub);

        $constraintValidatorFactory = new class($this->domain, $domainRouterFactoryStub) extends ConstraintValidatorFactory {
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
        };

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory($constraintValidatorFactory)
            ->getValidator();

        return [
            new ValidatorExtension($validator),
            new PreloadedExtension(
                [
                    new SeoGroupType($this->domain),
                    new SeoAttributesType(new SeoMetaRobotsEnum(), $this->domain),
                    new MultidomainType($this->domain, new FormTypeLayout()),
                    new UrlListType($this->createStub(FriendlyUrlFacade::class), $domainRouterFactoryStub, $this->domain),
                ],
                [],
            ),
        ];
    }

    private function getDomainSeoForm(FormInterface $form, int $domainId): FormInterface
    {
        return $form->get('seoGroup')->get('domains')->get((string)$domainId);
    }

    /**
     * @param array<string, mixed> $seoGroupOptions
     */
    private function createMultidomainForm(array $seoGroupOptions = [], ?BrandData $brandData = null): FormInterface
    {
        $namesBuilder = $this->factory->createNamedBuilder('names', FormType::class, null, ['mapped' => false]);

        foreach (self::LOCALES_BY_DOMAIN_ID as $locale) {
            $namesBuilder->add($locale, TextType::class);
        }

        return $this->factory->createNamedBuilder(self::ROOT_FORM_NAME, FormType::class, $brandData ?? new BrandData(), ['data_class' => BrandData::class])
            ->add($namesBuilder)
            ->add('seoGroup', SeoGroupType::class, $seoGroupOptions)
            ->getForm();
    }

    /**
     * @param array<string, mixed> $seoGroupOptions
     */
    private function createSingleDomainForm(ArticleData $articleData, array $seoGroupOptions = []): FormInterface
    {
        return $this->factory->createNamedBuilder(self::ROOT_FORM_NAME, FormType::class, $articleData, ['data_class' => ArticleData::class])
            ->add('name', TextType::class, ['mapped' => false])
            ->add('seoGroup', SeoGroupType::class, ['domain_id' => Domain::FIRST_DOMAIN_ID] + $seoGroupOptions)
            ->getForm();
    }
}
