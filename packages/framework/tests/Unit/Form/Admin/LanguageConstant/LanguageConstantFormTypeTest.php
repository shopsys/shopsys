<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\Admin\LanguageConstant;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\LinkType;
use Shopsys\FrameworkBundle\Form\Admin\LanguageConstant\LanguageConstantFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validation;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class LanguageConstantFormTypeTest extends TypeTestCase
{
    use SetTranslatorTrait;

    private UrlGeneratorInterface $urlGenerator;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function getValidUserTranslationsData(): iterable
    {
        yield 'starts with space' => [' user translation', ' user translation'];

        yield 'ends with space' => ['user translation ', 'user translation '];

        yield 'contains only space' => [' ', ' '];
    }

    #[DataProvider('getValidUserTranslationsData')]
    public function testUserTranslationAllowsRegularSpaces(
        string $userTranslation,
        string $expectedUserTranslation,
    ): void {
        $languageConstantFormData = $this->getFullLanguageConstantFormData();
        $languageConstantFormData['userTranslation'] = $userTranslation;

        $languageConstantForm = $this->createLanguageConstantForm();
        $languageConstantForm->submit($languageConstantFormData);

        $this->assertTrue($languageConstantForm->isValid(), 'Valid form');
        $this->assertSame($expectedUserTranslation, $languageConstantForm->getData()->userTranslation);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function getInvalidUserTranslationsData(): iterable
    {
        yield 'contains tab' => ["user\ttranslation"];

        yield 'contains newline' => ["user\ntranslation"];

        yield 'contains carriage return' => ["user\rtranslation"];

        yield 'contains non-breaking space' => ["user\u{00A0}translation"];
    }

    #[DataProvider('getInvalidUserTranslationsData')]
    public function testUserTranslationRejectsWhitespaceCharactersOtherThanRegularSpace(string $userTranslation): void
    {
        $languageConstantFormData = $this->getFullLanguageConstantFormData();
        $languageConstantFormData['userTranslation'] = $userTranslation;

        $languageConstantForm = $this->createLanguageConstantForm();
        $languageConstantForm->submit($languageConstantFormData);

        $this->assertFalse($languageConstantForm->isValid(), 'Invalid form');
    }

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);
        $this->setTranslator();

        $this->urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $this->urlGenerator->method('generate')->willReturn(DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL);

        parent::setUp();
    }

    #[Override]
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension(
                [
                    new LanguageConstantFormType(),
                    new DisplayOnlyType(),
                    new ActionBarType($this->urlGenerator),
                    new LinkType(),
                ],
                [],
            ),
        ];
    }

    /**
     * @return array{
     *     actionBar: array{save: string},
     *     userTranslation: string,
     * }
     */
    private function getFullLanguageConstantFormData(): array
    {
        return [
            'actionBar' => [
                'save' => '',
            ],
            'userTranslation' => 'user translation',
        ];
    }

    private function createLanguageConstantForm(): FormInterface
    {
        $languageConstantData = new LanguageConstantData();
        $languageConstantData->key = 'language constant key';
        $languageConstantData->namespace = LanguageConstant::NAMESPACE_COMMON;
        $languageConstantData->locale = 'en';
        $languageConstantData->originalTranslation = 'original translation';
        $languageConstantData->userTranslation = 'user translation';

        return $this->factory->create(LanguageConstantFormType::class, $languageConstantData);
    }
}
