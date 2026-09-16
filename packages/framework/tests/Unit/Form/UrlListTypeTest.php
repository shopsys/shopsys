<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;
use Tests\FrameworkBundle\Test\UrlListTypeTestTrait;

class UrlListTypeTest extends TypeTestCase
{
    use SetTranslatorTrait;
    use UrlListTypeTestTrait;

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

        $this->domainRouterFactory = $this->createDomainRouterFactoryMatchingNoRoute();

        parent::setUp();
    }

    public function testSubmittedNewUrlIsMapped(): void
    {
        $form = $this->createUrlListForm();
        $form->submit([
            'newUrls' => ['url-list-type-test-unique-slug'],
        ]);

        $this->assertTrue($form->isValid(), (string)$form->getErrors(true));

        /** @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData $urlListData */
        $urlListData = $form->getData();

        $this->assertSame('url-list-type-test-unique-slug', $urlListData->newUrls[0]);
    }

    public function testDuplicateNewUrlSlugsAreRejected(): void
    {
        $form = $this->createUrlListForm();
        $form->submit([
            'newUrls' => ['url-list-type-test-duplicate-slug', 'url-list-type-test-duplicate-slug'],
        ]);

        $this->assertFalse($form->isValid());
    }

    public function testChoiceFieldsAreNotAddedForEntityWithoutUrls(): void
    {
        $form = $this->createUrlListForm();

        $this->assertFalse($form->has('toDelete'));
        $this->assertFalse($form->has('mainFriendlyUrl'));
        $this->assertTrue($form->has('newUrls'));
    }

    #[Override]
    protected function getExtensions(): array
    {
        return [
            $this->createValidatorExtensionWithUniqueSlugsOnDomain($this->domain, $this->domainRouterFactory),
            new PreloadedExtension([$this->createUrlListType($this->domain, $this->domainRouterFactory)], []),
        ];
    }

    private function createUrlListForm(): FormInterface
    {
        return $this->factory->create(UrlListType::class, null, [
            'route_name' => 'front_brand_detail',
            'domain_id' => Domain::FIRST_DOMAIN_ID,
        ]);
    }
}
