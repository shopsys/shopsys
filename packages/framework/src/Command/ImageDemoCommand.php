<?php

namespace Shopsys\FrameworkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class ImageDemoCommand extends Command
{

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:image:demo';

    const EXIT_CODE_OK = 0;
    const EXIT_CODE_ERROR = 1;

    const IMAGES_TABLE_NAME = 'images';

    /**
     * @var string
     */
    private $demoImagesArchiveUrl;

    /**
     * @var string
     */
    private $demoImagesSqlUrl;

    /**
     * @var string
     */
    private $imagesDirectory;

    /**
     * @var string
     */
    private $domainImagesDirectory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $localFilesystem;

    /**
     * @var \League\Flysystem\MountManager
     */
    private $mountManager;
    
    public function __construct(
        string $demoImagesArchiveUrl,
        string $demoImagesSqlUrl,
        string $imagesDirectory,
        string $domainImagesDirectory,
        FilesystemInterface $filesystem,
        Filesystem $symfonyFilesystem,
        EntityManagerInterface $em,
        MountManager $mountManager
    ) {
        $this->demoImagesArchiveUrl = $demoImagesArchiveUrl;
        $this->demoImagesSqlUrl = $demoImagesSqlUrl;
        $this->imagesDirectory = $imagesDirectory;
        $this->domainImagesDirectory = $domainImagesDirectory;
        $this->filesystem = $filesystem;
        $this->localFilesystem = $symfonyFilesystem;
        $this->em = $em;
        $this->mountManager = $mountManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Download demo images');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $localArchiveFilepath = sys_get_temp_dir() . '/' . 'demoImages.zip';
        $temporaryImagesDirectory = sys_get_temp_dir() . '/img/';
        $unpackedDomainImagesDirectory = $temporaryImagesDirectory . 'domain/';

        $isCompleted = false;

        if (!$this->isImagesTableEmpty()) {
            $symfonyStyleIo = new SymfonyStyle($input, $output);
            $questionHelper = $this->getHelper('question');
            /* @var $questionHelper \Symfony\Component\Console\Helper\QuestionHelper*/

            $question = 'There are some images in your database. Those images will be deleted in order to install demo images. Do you wish to proceed? [YES]';
            $truncateImagesQuestion = new ConfirmationQuestion($question);
            if (!$questionHelper->ask($input, $output, $truncateImagesQuestion)) {
                $symfonyStyleIo->note('Demo images were not loaded, you need to truncate "' . self::IMAGES_TABLE_NAME . '" DB table first.');

                return self::EXIT_CODE_ERROR;
            }
            $this->truncateImagesFromDb();
            $symfonyStyleIo->note('DB table "' . self::IMAGES_TABLE_NAME . '" has been truncated.');
        }

        if ($this->downloadImages($output, $this->demoImagesArchiveUrl, $localArchiveFilepath)) {
            if ($this->unpackImages($output, $temporaryImagesDirectory, $localArchiveFilepath)) {
                $this->moveFilesFromLocalFilesystemToFilesystem($unpackedDomainImagesDirectory, $this->domainImagesDirectory);
                $this->moveFilesFromLocalFilesystemToFilesystem($temporaryImagesDirectory, $this->imagesDirectory);
                $this->loadDbChanges($output, $this->demoImagesSqlUrl);
                $isCompleted = true;
            }
        }

        $this->cleanUp($output, [$localArchiveFilepath, $unpackedDomainImagesDirectory]);

        return $isCompleted ? self::EXIT_CODE_OK : self::EXIT_CODE_ERROR;
    }
    
    private function unpackImages(OutputInterface $output, string $imagesPath, string $localArchiveFilepath): bool
    {
        $zipArchive = new ZipArchive();

        $result = $zipArchive->open($localArchiveFilepath);
        if ($result !== true) {
            $output->writeln('<fg=red>Unpacking of images archive failed</fg=red>');
            return false;
        }

        $zipArchive->extractTo($imagesPath);
        $zipArchive->close();
        $output->writeln('<fg=green>Unpacking of images archive was successfully completed</fg=green>');

        return true;
    }
    
    private function loadDbChanges(OutputInterface $output, string $sqlUrl): void
    {
        $fileContents = file_get_contents($sqlUrl);
        if ($fileContents === false) {
            $output->writeln('<fg=red>Download of DB changes failed</fg=red>');
            return;
        }
        $sqlQueries = explode(';', $fileContents);
        $sqlQueries = array_map('trim', $sqlQueries);
        $sqlQueries = array_filter($sqlQueries);

        $rsm = new ResultSetMapping();
        foreach ($sqlQueries as $sqlQuery) {
            $this->em->createNativeQuery($sqlQuery, $rsm)->execute();
        }
        $output->writeln('<fg=green>DB changes were successfully applied (queries: ' . count($sqlQueries) . ')</fg=green>');
    }
    
    private function downloadImages(OutputInterface $output, string $archiveUrl, string $localArchiveFilepath): bool
    {
        $output->writeln('Start downloading demo images');

        try {
            $this->localFilesystem->copy($archiveUrl, $localArchiveFilepath, true);
        } catch (Exception $e) {
            $output->writeln('<fg=red>Downloading of demo images failed</fg=red>');
            $output->writeln('<fg=red>Exception: ' . $e->getMessage() . '</fg=red>');

            return false;
        }

        $output->writeln('Success downloaded');
        return true;
    }

    /**
     * @param string[] $pathsToRemove
     */
    private function cleanUp(OutputInterface $output, $pathsToRemove): void
    {
        try {
            $this->localFilesystem->remove($pathsToRemove);
        } catch (Exception $e) {
            $output->writeln('<fg=red>Deleting of demo archive in cache failed</fg=red>');
            $output->writeln('<fg=red>Exception: ' . $e->getMessage() . '</fg=red>');
        }
    }
    
    private function moveFilesFromLocalFilesystemToFilesystem(string $origin, string $target): void
    {
        $finder = new Finder();
        $finder->files()->in($origin);
        foreach ($finder as $file) {
            $filepath = $file->getPathname();

            if ($this->localFilesystem->exists($filepath)) {
                $newFilepath = $target . $file->getRelativePathname();

                if ($this->filesystem->has($newFilepath)) {
                    $this->filesystem->delete($newFilepath);
                }
                $this->mountManager->move('local://' . $filepath, 'main://' . $newFilepath);
            }
        }
    }

    private function isImagesTableEmpty(): bool
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total_count', 'totalCount');
        // COUNT() returns BIGINT which is hydrated into string on 32-bit architecture
        $nativeQuery = $this->em->createNativeQuery('SELECT COUNT(*)::INTEGER AS total_count FROM ' . self::IMAGES_TABLE_NAME, $rsm);
        $imagesCount = $nativeQuery->getSingleScalarResult();

        return $imagesCount === 0;
    }

    private function truncateImagesFromDb(): void
    {
        $this->em->createNativeQuery('TRUNCATE TABLE ' . self::IMAGES_TABLE_NAME, new ResultSetMapping())->execute();
    }
}
