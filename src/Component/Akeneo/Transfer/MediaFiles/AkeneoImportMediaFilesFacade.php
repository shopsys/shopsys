<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Transfer\MediaFiles;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

class AkeneoImportMediaFilesFacade extends AbstractAkeneoImportTransfer
{
    protected const DS = DIRECTORY_SEPARATOR;

    /**
     * @var \App\Component\Akeneo\Transfer\MediaFiles\MediaFilesTransferAkeneoFacade
     */
    private $mediaFilesTransferAkeneoFacade;

    /**
     * @var string|null
     */
    private $downloadCode;

    /**
     * @var string|null
     */
    private $dictionaryPath;

    /**
     * @var string|null
     */
    private $fileName;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $localFilesystem;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Component\Akeneo\Transfer\MediaFiles\MediaFilesTransferAkeneoFacade $mediaFilesTransferAkeneoFacade
     * @param \League\Flysystem\FilesystemInterface $localFilesystem
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        MediaFilesTransferAkeneoFacade $mediaFilesTransferAkeneoFacade,
        FilesystemInterface $localFilesystem
    ) {
        parent::__construct($akeneoImportTransferDependency);
        $this->mediaFilesTransferAkeneoFacade = $mediaFilesTransferAkeneoFacade;
        $this->localFilesystem = $localFilesystem;
    }

    /**
     * @param array $akeneoData
     */
    protected function processItem(array $akeneoData): void
    {
        $this->storeFile($this->getFullFilepathWithName(), $akeneoData[0]);
    }

    /**
     * @param string $fullFilepathWithName
     * @param string $content
     */
    private function storeFile(string $fullFilepathWithName, string $content)
    {
        try {
            $this->localFilesystem->write($fullFilepathWithName, $content);
            $this->logger->addInfo('File was successfully stored.');
        } catch (FileExistsException $exception) {
            try {
                $this->localFilesystem->delete($fullFilepathWithName);
            } catch (FileNotFoundException $exception) {
            }

            $this->storeFile($fullFilepathWithName, $content);
        } catch (\Throwable $exception) {
            $this->logger->addError('File save failed', [
                'reason' => $exception->getMessage(),
                'code' => $this->downloadCode,
                'dictionary' => $this->dictionaryPath,
                'filename' => $this->fileName,
            ]);
        }
    }

    /**
     * @return string
     */
    public function getFullFilepathWithName(): string
    {
        return $this->getDictionary() . self::DS . $this->fileName;
    }

    /**
     * @return string
     */
    private function getDictionary(): string
    {
        if (!$this->localFilesystem->has($this->dictionaryPath)) {
            $this->logger->addInfo('Create new dictionary "' . $this->dictionaryPath . '"');
            $this->localFilesystem->createDir($this->dictionaryPath);
        }

        return $this->dictionaryPath;
    }

    protected function doBeforeTransfer(): void
    {
        if ($this->downloadCode === null) {
            $this->logger->addWarning('Skipping download, "code" isn\'t set.');
            return;
        }

        $this->logger->addInfo('Transfer media file data from Akeneo ...');
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Transfer is done.');
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {
        $this->logger->addInfo(sprintf('Getting data from API for media file : %s', $this->downloadCode));

        yield $this->mediaFilesTransferAkeneoFacade->getMediaFile($this->downloadCode)->getBody();
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'mediaFilesTransfer';
    }

    /**
     * @param string $code
     * @param string $dictionaryPath
     * @param string $fileName
     */
    public function downloadMediaFile(string $code, string $dictionaryPath, string $fileName): void
    {
        $this->fileName = trim($fileName, self::DS);
        $this->dictionaryPath = self::DS . trim($dictionaryPath, self::DS) . self::DS;
        $this->downloadCode = $code;
        $this->runTransfer();
    }
}
