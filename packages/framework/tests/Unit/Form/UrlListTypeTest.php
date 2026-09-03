<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomains;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomainsValidator;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Validation;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class UrlListTypeTest extends TypeTestCase
{
    use SetTranslatorTrait;

    private Domain $domain;

    private DomainRouterFactory $domainRouterFactory;

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);

        $this->setTranslator();

        $administratorStub = $this->createStub(Administrator::class);
        $administratorStub->method('getDisplayOnlyDomainIds')->willReturn([]);

        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);
        $currentAdministratorStub->method('getCurrentlyLoggedAdministrator')->willReturn($administratorStub);

        $this->domain = new Domain(
            [
                DomainConfigHelper::getDomainConfig(),
                DomainConfigHelper::getDomainConfig(id: Domain::SECOND_DOMAIN_ID, locale: 'en'),
            ],
            $this->createStub(Setting::class),
            $currentAdministratorStub,
        );

        $routerStub = $this->createStub(DomainRouter::class);
        $routerStub->method('match')->willReturnCallback(static function (): never {
            throw new ResourceNotFoundException();
        });

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);
        $domainRouterFactoryStub->method('getRouter')->willReturn($routerStub);
        $this->domainRouterFactory = $domainRouterFactoryStub;

        parent::setUp();
    }

    public function testSubmittedNewUrlIsMappedUnderItsDomainId(): void
    {
        $form = $this->createUrlListForm();
        $form->submit([
            'newUrls' => [
                '1' => [
                    ['slug' => 'url-list-type-test-unique-slug'],
                ],
            ],
        ]);

        $this->assertTrue($form->isValid(), (string)$form->getErrors(true));

        /** @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData $urlListData */
        $urlListData = $form->getData();

        $this->assertSame('url-list-type-test-unique-slug', $urlListData->newUrls[1][0][UrlListData::FIELD_SLUG]);
    }

    public function testNewUrlWithNotEnabledDomainIsRejected(): void
    {
        $form = $this->createUrlListForm();
        $form->submit([
            'newUrls' => [
                '999' => [
                    ['slug' => 'url-list-type-test-unique-slug'],
                ],
            ],
        ]);

        $this->assertFalse($form->isValid());
    }

    public function testDuplicateNewUrlSlugsOnSameDomainAreRejected(): void
    {
        $form = $this->createUrlListForm();
        $form->submit([
            'newUrls' => [
                '1' => [
                    ['slug' => 'url-list-type-test-duplicate-slug'],
                    ['slug' => 'url-list-type-test-duplicate-slug'],
                ],
            ],
        ]);

        $this->assertFalse($form->isValid());
    }

    #[Override]
    protected function getExtensions(): array
    {
        $constraintValidatorFactory = new class($this->domain, $this->domainRouterFactory) extends ConstraintValidatorFactory implements ConstraintValidatorFactoryInterface {
            public function __construct(
                private readonly Domain $domain,
                private readonly DomainRouterFactory $domainRouterFactory,
            ) {
                parent::__construct();
            }

            #[Override]
            public function getInstance(Constraint $constraint): ConstraintValidatorInterface
            {
                if ($constraint instanceof UniqueSlugsOnDomains) {
                    return new UniqueSlugsOnDomainsValidator($this->domain, $this->domainRouterFactory);
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
                    new UrlListType(
                        $this->createStub(FriendlyUrlFacade::class),
                        $this->domainRouterFactory,
                        $this->domain,
                    ),
                ],
                [],
            ),
        ];
    }

    private function createUrlListForm(): FormInterface
    {
        return $this->factory->create(UrlListType::class, null, [
            'route_name' => 'front_brand_detail',
        ]);
    }
}
