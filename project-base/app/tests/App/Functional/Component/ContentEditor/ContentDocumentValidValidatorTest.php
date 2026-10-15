<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\ContentEditor;

use Shopsys\AdministrationBundle\Component\ContentEditor\Validation\ContentDocumentValid;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\App\Test\TransactionFunctionalTestCase;

class ContentDocumentValidValidatorTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ValidatorInterface $validator;

    /**
     * @inject
     */
    private ContentDocumentFactory $contentDocumentFactory;

    public function testDocumentWithInvalidBlockHasOneViolation(): void
    {
        $document = $this->contentDocumentFactory->createFromArray([
            'blocks' => [
                ['type' => 'text', 'data' => ['html' => '<p>Fine</p>']],
                ['type' => 'video', 'data' => ['url' => 'https://example.com/not-a-video']],
            ],
        ]);

        $violations = $this->validator->validate($document, new ContentDocumentValid());

        $this->assertCount(1, $violations);
        $this->assertSame('The content has 1 invalid block(s), fix them in the editor', $violations[0]->getMessage());
    }

    public function testDocumentWithEmptyRequiredFieldHasOneViolation(): void
    {
        $document = $this->contentDocumentFactory->createFromArray([
            'blocks' => [
                ['type' => 'video', 'data' => ['url' => '']],
            ],
        ]);

        $violations = $this->validator->validate($document, new ContentDocumentValid());

        $this->assertCount(1, $violations);
    }

    public function testValidDocumentHasNoViolation(): void
    {
        $document = $this->contentDocumentFactory->createFromArray([
            'blocks' => [
                ['type' => 'text', 'data' => ['html' => '<p>Fine</p>']],
                ['type' => 'video', 'data' => ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']],
            ],
        ]);

        $this->assertCount(0, $this->validator->validate($document, new ContentDocumentValid()));
    }
}
