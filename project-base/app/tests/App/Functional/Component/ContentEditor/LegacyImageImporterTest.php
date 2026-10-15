<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\ContentEditor;

use Shopsys\FrameworkBundle\Component\ContentEditor\Migration\LegacyImageImporter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class LegacyImageImporterTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private LegacyImageImporter $legacyImageImporter;

    /**
     * @inject
     */
    private MediaFacade $mediaFacade;

    public function testImageFromTheContentDirectoryIsImportedToMedia(): void
    {
        $mediaId = $this->legacyImageImporter->import($this->getWysiwygUrl('test-image.jpg'));

        $this->assertIsInt($mediaId);

        $media = $this->mediaFacade->getById($mediaId);

        $this->assertSame('test-image', $media->getTitle());
        $this->assertGreaterThan(0, $media->getWidth());
        $this->assertGreaterThan(0, $media->getHeight());
    }

    public function testRelativeUrlIsImportedOnlyOncePerRun(): void
    {
        $url = '/' . $this->getContentDirName() . '/wysiwyg/test-image.jpg';

        $this->assertSame($this->legacyImageImporter->import($url), $this->legacyImageImporter->import($url));
    }

    public function testUnknownFileIsNotImported(): void
    {
        $this->assertNull($this->legacyImageImporter->import($this->getWysiwygUrl('missing.jpg')));
    }

    public function testFileOutsideOfTheContentDirectoryIsNotImported(): void
    {
        $this->assertNull($this->legacyImageImporter->import('https://other.example.com/' . $this->getContentDirName() . '/wysiwyg/test-image.jpg'));
        $this->assertNull($this->legacyImageImporter->import('/' . $this->getContentDirName() . '/../../composer.json'));
        $this->assertNull($this->legacyImageImporter->import('/composer.json'));
    }

    private function getWysiwygUrl(string $filename): string
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getUrl() . '/' . $this->getContentDirName() . '/wysiwyg/' . $filename;
    }

    private function getContentDirName(): string
    {
        return $this->getContainer()->getParameter('shopsys.content_dir_name');
    }
}
