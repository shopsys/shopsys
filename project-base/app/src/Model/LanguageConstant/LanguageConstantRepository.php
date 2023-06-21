<?php

declare(strict_types=1);

namespace App\Model\LanguageConstant;

use App\Model\LanguageConstant\Exception\LanguageConstantNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class LanguageConstantRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function getTranslationsByLocaleIndexedByKey(string $locale): array
    {
        $languageConstants = $this->getRepository()
            ->createQueryBuilder('c')
            ->select('c.key, ct.translation')
            ->join(LanguageConstantTranslation::class, 'ct', Join::WITH, 'ct.translatable = c AND ct.locale = :locale')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();

        return array_column($languageConstants, 'translation', 'key');
    }

    /**
     * @param string $key
     * @return \App\Model\LanguageConstant\LanguageConstant
     */
    public function getByKey(string $key): LanguageConstant
    {
        $languageConstant = $this->findByKey($key);

        if ($languageConstant === null) {
            throw new LanguageConstantNotFoundException(sprintf('Language constant with key "%s" not found', $key));
        }

        return $languageConstant;
    }

    /**
     * @param string $key
     * @return \App\Model\LanguageConstant\LanguageConstant|null
     */
    public function findByKey(string $key): ?LanguageConstant
    {
        return $this->getRepository()->findOneBy(['key' => $key]);
    }

    /**
     * @param int $languageConstantId
     * @return bool
     */
    public function hasTranslationsByLanguageConstantId(int $languageConstantId): bool
    {
        return $this->getTranslationRepository()
            ->createQueryBuilder('ct')
            ->select('CASE WHEN COUNT(ct.id) > 0 THEN TRUE ELSE FALSE END')
            ->where('ct.translatable = :translatableId')
            ->setParameter('translatableId', $languageConstantId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(LanguageConstant::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getTranslationRepository(): EntityRepository
    {
        return $this->em->getRepository(LanguageConstantTranslation::class);
    }
}
