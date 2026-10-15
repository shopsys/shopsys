<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Validation;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Validation\ContentDocumentContainsVariable;
use Shopsys\FrameworkBundle\Component\ContentEditor\Validation\ContentDocumentContainsVariableValidator;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ContentDocumentContainsVariableValidatorTest extends ConstraintValidatorTestCase
{
    public function testVariableInTextIsFound(): void
    {
        $document = $this->createDocument([new ContentBlockInstance('a', 'mail-text', ['html' => '<p>Order <strong>{order_number}</strong></p>'])]);

        $this->validator->validate($document, new ContentDocumentContainsVariable('{order_number}'));

        $this->assertNoViolation();
    }

    public function testVariableInButtonUrlIsFound(): void
    {
        $document = $this->createDocument([new ContentBlockInstance('a', 'mail-button', ['text' => 'Detail', 'url' => '{order_url}'])]);

        $this->validator->validate($document, new ContentDocumentContainsVariable('{order_url}'));

        $this->assertNoViolation();
    }

    public function testVariableInLinkOfRichTextIsFound(): void
    {
        $document = $this->createDocument([new ContentBlockInstance('a', 'mail-text', ['html' => '<p><a href="{order_url}">Detail</a></p>'])]);

        $this->validator->validate($document, new ContentDocumentContainsVariable('{order_url}'));

        $this->assertNoViolation();
    }

    public function testVariableInVariableFieldOfBlockInColumnIsFound(): void
    {
        $document = $this->createDocument([
            new ContentBlockInstance('a', 'mail-columns', [], [
                [],
                [new ContentBlockInstance('b', 'mail-image-variable', ['variable' => '{product_image}', 'alt' => ''])],
            ]),
        ]);

        $this->validator->validate($document, new ContentDocumentContainsVariable('{product_image}'));

        $this->assertNoViolation();
    }

    public function testMissingVariableIsInvalid(): void
    {
        $document = $this->createDocument([new ContentBlockInstance('a', 'mail-text', ['html' => '<p>Hello</p>'])]);

        $this->validator->validate($document, new ContentDocumentContainsVariable('{order_number}'));

        $this->buildViolation('Variable {{ needle }} is required')->setParameter('{{ needle }}', '{order_number}')->assertRaised();
    }

    public function testMissingDocumentIsInvalid(): void
    {
        $this->validator->validate(null, new ContentDocumentContainsVariable('{order_number}'));

        $this->buildViolation('Variable {{ needle }} is required')->setParameter('{{ needle }}', '{order_number}')->assertRaised();
    }

    #[Override]
    protected function createValidator(): ConstraintValidatorInterface
    {
        $translator = $this->createStub(Translator::class);
        $translator->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translator);

        return new ContentDocumentContainsVariableValidator();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     */
    private function createDocument(array $blocks): ContentDocument
    {
        return new ContentDocument(1, 'mail', $blocks);
    }
}
