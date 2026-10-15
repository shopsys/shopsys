<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Validation;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Validation\ContentDocumentNotEmpty;
use Shopsys\FrameworkBundle\Component\ContentEditor\Validation\ContentDocumentNotEmptyValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ContentDocumentNotEmptyValidatorTest extends ConstraintValidatorTestCase
{
    public function testDocumentWithBlockIsValid(): void
    {
        $document = new ContentDocument(1, 'web', [new ContentBlockInstance('a', 'text', ['html' => '<p>x</p>'])]);

        $this->validator->validate($document, new ContentDocumentNotEmpty());

        $this->assertNoViolation();
    }

    public function testEmptyDocumentIsInvalid(): void
    {
        $this->validator->validate(new ContentDocument(1, 'web', []), new ContentDocumentNotEmpty());

        $this->buildViolation('Please enter article content')->assertRaised();
    }

    public function testMissingDocumentIsInvalid(): void
    {
        $this->validator->validate(null, new ContentDocumentNotEmpty());

        $this->buildViolation('Please enter article content')->assertRaised();
    }

    #[Override]
    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ContentDocumentNotEmptyValidator();
    }
}
