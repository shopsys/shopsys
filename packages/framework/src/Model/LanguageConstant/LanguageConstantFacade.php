<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use function GuzzleHttp\json_decode;

class LanguageConstantFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantRepository $languageConstantRepository
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantFactory $languageConstantFactory
     * @param array<string, string> $translationNamespaces
     * @param string $domainLocalesDirectory
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly LanguageConstantRepository $languageConstantRepository,
        protected readonly LanguageConstantFactory $languageConstantFactory,
        protected readonly array $translationNamespaces,
        protected readonly string $domainLocalesDirectory,
        protected readonly FilesystemOperator $filesystem,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param string $locale
     * @param string $namespace
     * @return string[]
     */
    public function getOriginalTranslationsByLocaleIndexedByKey(string $locale, string $namespace): array
    {
        if (!isset($this->translationNamespaces[$namespace])) {
            throw new InvalidArgumentException(sprintf('Translation namespace "%s" is not configured.', $namespace));
        }

        $url = sprintf($this->translationNamespaces[$namespace], $locale);

        return json_decode((new Client())->get($url)->getBody()->getContents(), true);
    }

    /**
     * @param string $locale
     * @return array<string, string[]>
     */
    public function getAllOriginalTranslationsByLocaleIndexedByNamespace(string $locale): array
    {
        $allTranslations = [];

        foreach ($this->translationNamespaces as $namespace => $urlPattern) {
            try {
                $allTranslations[$namespace] = $this->getOriginalTranslationsByLocaleIndexedByKey($locale, $namespace);
            } catch (Exception $e) {
                $this->logger->warning('Failed to load translations for namespace', [
                    'namespace' => $namespace,
                    'locale' => $locale,
                    'error' => $e->getMessage(),
                ]);
                // If namespace file doesn't exist, skip it
                $allTranslations[$namespace] = [];
            }
        }

        return $allTranslations;
    }

    /**
     * @param string $locale
     * @param string $namespace
     * @return string[]
     */
    public function getUserTranslationsByLocaleIndexedByKey(string $locale, string $namespace): array
    {
        return $this->languageConstantRepository->getTranslationsByLocaleIndexedByKey($locale, $namespace);
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function getAllUserTranslationsByLocaleIndexedByNamespacedKey(string $locale): array
    {
        return $this->languageConstantRepository->getAllTranslationsByLocaleIndexedByNamespacedKey($locale);
    }

    /**
     * @param string $key
     * @param string $namespace
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant|null
     */
    public function findByKey(string $key, string $namespace): ?LanguageConstant
    {
        return $this->languageConstantRepository->findByKey($key, $namespace);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData $languageConstantData
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant|null $languageConstant
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant
     */
    public function createOrEdit(
        LanguageConstantData $languageConstantData,
        ?LanguageConstant $languageConstant,
    ): LanguageConstant {
        $languageConstant = $languageConstant === null ? $this->create($languageConstantData) : $this->edit($languageConstantData);
        $this->cleanStorefrontCacheFacade->cleanStorefrontTranslationCache($languageConstantData->locale, $languageConstantData->namespace);

        return $languageConstant;
    }

    /**
     * @param string $key
     * @param string $locale
     * @param string $namespace
     */
    public function delete(string $key, string $locale, string $namespace): void
    {
        $languageConstant = $this->languageConstantRepository->getByKey($key, $namespace);
        $languageConstant->deleteTranslation($locale);

        $this->em->flush();

        if ($this->languageConstantRepository->hasTranslationsByLanguageConstantId($languageConstant->getId())) {
            return;
        }

        $this->em->remove($languageConstant);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontTranslationCache($locale, $namespace);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData $languageConstantData
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant
     */
    protected function create(LanguageConstantData $languageConstantData): LanguageConstant
    {
        $languageConstant = $this->languageConstantFactory->create($languageConstantData);

        $this->em->persist($languageConstant);
        $this->em->flush();

        return $languageConstant;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData $languageConstantData
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant
     */
    protected function edit(LanguageConstantData $languageConstantData): LanguageConstant
    {
        $languageConstant = $this->languageConstantRepository->getByKey($languageConstantData->key, $languageConstantData->namespace);
        $languageConstant->editTranslation($languageConstantData);

        $this->em->flush();

        return $languageConstant;
    }

    /**
     * Generate all namespace-specific translation files for the given locale
     *
     * @param string $locale
     */
    public function generateAllNamespaceFiles(string $locale): void
    {
        $targetFilePath = $this->domainLocalesDirectory . $locale;

        if (!$this->filesystem->has($targetFilePath)) {
            $this->filesystem->createDirectory($targetFilePath, ['directory_visibility' => 'public']);
        }

        // Generate file for each configured namespace
        foreach (array_keys($this->translationNamespaces) as $namespace) {
            $userTranslations = $this->getUserTranslationsByLocaleIndexedByKey($locale, $namespace);
            $translations = json_encode($userTranslations);
            $fileName = $namespace . '.json';
            $this->filesystem->write($targetFilePath . '/' . $fileName, $translations);
        }
    }
}
