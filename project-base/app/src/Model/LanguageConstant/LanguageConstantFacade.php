<?php

declare(strict_types=1);

namespace App\Model\LanguageConstant;

use App\Component\Redis\CleanStorefrontCacheFacade;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use League\Flysystem\FilesystemOperator;
use function GuzzleHttp\json_decode;

class LanguageConstantFacade
{
    private const GENERATED_FILE_NAME = 'common.json';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\LanguageConstant\LanguageConstantRepository $languageConstantRepository
     * @param \App\Model\LanguageConstant\LanguageConstantFactory $languageConstantFactory
     * @param string $languageConstantsUrlPattern
     * @param string $domainLocalesDirectory
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \App\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LanguageConstantRepository $languageConstantRepository,
        private readonly LanguageConstantFactory $languageConstantFactory,
        private readonly string $languageConstantsUrlPattern,
        private readonly string $domainLocalesDirectory,
        private readonly FilesystemOperator $filesystem,
        private readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function getOriginalTranslationsByLocaleIndexedByKey(string $locale): array
    {
        $url = sprintf($this->languageConstantsUrlPattern, $locale);

        return json_decode((new Client())->get($url)->getBody()->getContents(), true);
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function getUserTranslationsByLocaleIndexedByKey(string $locale): array
    {
        return $this->languageConstantRepository->getTranslationsByLocaleIndexedByKey($locale);
    }

    /**
     * @param string $key
     * @return \App\Model\LanguageConstant\LanguageConstant|null
     */
    public function findByKey(string $key): ?LanguageConstant
    {
        return $this->languageConstantRepository->findByKey($key);
    }

    /**
     * @param \App\Model\LanguageConstant\LanguageConstantData $languageConstantData
     * @param \App\Model\LanguageConstant\LanguageConstant|null $languageConstant
     * @return \App\Model\LanguageConstant\LanguageConstant
     */
    public function createOrEdit(
        LanguageConstantData $languageConstantData,
        ?LanguageConstant $languageConstant,
    ): LanguageConstant {
        $languageConstant = $languageConstant === null ? $this->create($languageConstantData) : $this->edit($languageConstantData);
        $this->cleanStorefrontCacheFacade->cleanStorefrontTranslationCache($languageConstantData->locale);

        return $languageConstant;
    }

    /**
     * @param string $key
     * @param string $locale
     */
    public function delete(string $key, string $locale): void
    {
        $languageConstant = $this->languageConstantRepository->getByKey($key);
        $languageConstant->deleteTranslation($locale);

        $this->em->flush();

        if ($this->languageConstantRepository->hasTranslationsByLanguageConstantId($languageConstant->getId())) {
            return;
        }

        $this->em->remove($languageConstant);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontTranslationCache($locale);
    }

    /**
     * @param \App\Model\LanguageConstant\LanguageConstantData $languageConstantData
     * @return \App\Model\LanguageConstant\LanguageConstant
     */
    private function create(LanguageConstantData $languageConstantData): LanguageConstant
    {
        $languageConstant = $this->languageConstantFactory->create($languageConstantData);

        $this->em->persist($languageConstant);
        $this->em->flush();

        return $languageConstant;
    }

    /**
     * @param \App\Model\LanguageConstant\LanguageConstantData $languageConstantData
     * @return \App\Model\LanguageConstant\LanguageConstant
     */
    private function edit(LanguageConstantData $languageConstantData): LanguageConstant
    {
        $languageConstant = $this->languageConstantRepository->getByKey($languageConstantData->key);
        $languageConstant->editTranslation($languageConstantData);

        $this->em->flush();

        return $languageConstant;
    }

    /**
     * @param string $locale
     */
    public function generateLanguageConstantFile(string $locale): void
    {
        $userTranslations = $this->getUserTranslationsByLocaleIndexedByKey($locale);
        $translations = json_encode($userTranslations);
        $targetFilePath = $this->domainLocalesDirectory . $locale;

        if (!$this->filesystem->has($targetFilePath)) {
            $this->filesystem->createDirectory($targetFilePath, ['directory_visibility' => 'public']);
        }

        $this->filesystem->write($targetFilePath . '/' . self::GENERATED_FILE_NAME, $translations);
    }
}
