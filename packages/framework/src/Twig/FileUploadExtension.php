<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileUploadExtension extends AbstractExtension
{
    public function __construct(protected readonly FileUpload $fileUpload)
    {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getLabelByTemporaryFilename', $this->getLabelByTemporaryFilename(...)),
        ];
    }

    public function getLabelByTemporaryFilename(string $temporaryFilename): string
    {
        $filename = $this->fileUpload->getOriginalFilenameByTemporary($temporaryFilename);
        $filesizeInBytes = $this->fileUpload->getTemporaryFilesize($temporaryFilename);

        if ($filesizeInBytes > 0) {
            $filesizeInMegabytes = round($filesizeInBytes / 1000 / 1000, 2); // https://en.wikipedia.org/wiki/Binary_prefix

            return $filename . ' (' . $filesizeInMegabytes . ' MB)';
        }

        return $filename;
    }

    public function getName(): string
    {
        return 'fileupload_extension';
    }
}
