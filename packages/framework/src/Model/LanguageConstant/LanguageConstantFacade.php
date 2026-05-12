<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;
use Nette\Utils\Json;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;

class LanguageConstantFacade
{
    /**
     * @param array<string, string> $translationNamespaces
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
     * @return string[]
     */
    public function getOriginalTranslationsByLocaleIndexedByKey(string $locale, string $namespace): array
    {
        if (!isset($this->translationNamespaces[$namespace])) {
            throw new InvalidArgumentException(sprintf('Translation namespace "%s" is not configured.', $namespace));
        }

        $url = sprintf($this->translationNamespaces[$namespace], $locale);

        return Json::decode((new Client())->get($url)->getBody()->getContents(), true);
    }

    /**
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
     * @return string[]
     */
    public function getUserTranslationsByLocaleIndexedByKey(string $locale, string $namespace): array
    {
        return $this->languageConstantRepository->getTranslationsByLocaleIndexedByKey($locale, $namespace);
    }

    /**
     * @return string[]
     */
    public function getAllUserTranslationsByLocaleIndexedByNamespacedKey(string $locale): array
    {
        return $this->languageConstantRepository->getAllTranslationsByLocaleIndexedByNamespacedKey($locale);
    }

    public function findByKey(string $key, string $namespace): ?LanguageConstant
    {
        return $this->languageConstantRepository->findByKey($key, $namespace);
    }

    public function createOrEdit(
        LanguageConstantData $languageConstantData,
        ?LanguageConstant $languageConstant,
    ): LanguageConstant {
        return $languageConstant === null ? $this->create($languageConstantData) : $this->edit($languageConstantData);
    }

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
    }

    protected function create(LanguageConstantData $languageConstantData): LanguageConstant
    {
        $languageConstant = $this->languageConstantFactory->create($languageConstantData);

        $this->em->persist($languageConstant);
        $this->em->flush();

        return $languageConstant;
    }

    protected function edit(LanguageConstantData $languageConstantData): LanguageConstant
    {
        $languageConstant = $this->languageConstantRepository->getByKey($languageConstantData->key, $languageConstantData->namespace);
        $languageConstant->editTranslation($languageConstantData);

        $this->em->flush();

        return $languageConstant;
    }

    /**
     * Generate all namespace-specific translation files for the given locale
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
            $translations = json_encode((object)$userTranslations, JSON_THROW_ON_ERROR);
            $fileName = $namespace . '.json';
            $this->filesystem->write($targetFilePath . '/' . $fileName, $translations);
        }
    }

    /**
     * @throws \JsonException
     */
    public function generateAllNamespaceFilesAndCleanStorefrontCache(string $locale, ?string $namespace = null): void
    {
        $this->generateAllNamespaceFiles($locale);
        $this->cleanStorefrontCacheFacade->cleanStorefrontTranslationCache($locale, $namespace);
    }
}
