<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\LanguageConstant\Exception\LanguageConstantNotFoundException;

class LanguageConstantRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * @param string $locale
     * @param string $namespace
     * @return string[]
     */
    public function getTranslationsByLocaleIndexedByKey(string $locale, string $namespace): array
    {
        $queryBuilder = $this->createTranslationQueryBuilder($locale, 'c.key, ct.translation')
            ->andWhere('c.namespace = :namespace')
            ->setParameter('namespace', $namespace);

        $languageConstants = $queryBuilder->getQuery()->getResult();

        return array_column($languageConstants, 'translation', 'key');
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function getAllTranslationsByLocaleIndexedByNamespacedKey(string $locale): array
    {
        $queryBuilder = $this->createTranslationQueryBuilder($locale, 'c.key, c.namespace, ct.translation');
        $languageConstants = $queryBuilder->getQuery()->getResult();

        $result = [];

        foreach ($languageConstants as $constant) {
            $namespacedKey = $this->createNamespacedKey($constant['namespace'], $constant['key']);
            $result[$namespacedKey] = $constant['translation'];
        }

        return $result;
    }

    /**
     * @param string $key
     * @param string $namespace
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant
     */
    public function getByKey(string $key, string $namespace): LanguageConstant
    {
        $languageConstant = $this->findByKey($key, $namespace);

        if ($languageConstant === null) {
            throw new LanguageConstantNotFoundException(sprintf('Language constant with key "%s" and namespace "%s" not found', $key, $namespace));
        }

        return $languageConstant;
    }

    /**
     * @param string $key
     * @param string $namespace
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant|null
     */
    public function findByKey(string $key, string $namespace): ?LanguageConstant
    {
        return $this->getRepository()->findOneBy(['key' => $key, 'namespace' => $namespace]);
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
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(LanguageConstant::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getTranslationRepository(): EntityRepository
    {
        return $this->em->getRepository(LanguageConstantTranslation::class);
    }

    /**
     * @param string $namespace
     * @param string $key
     * @return string
     */
    public function createNamespacedKey(string $namespace, string $key): string
    {
        return $namespace . '::' . $key;
    }

    /**
     * @param string $locale
     * @param string $selectFields
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createTranslationQueryBuilder(string $locale, string $selectFields): QueryBuilder
    {
        return $this->getRepository()
            ->createQueryBuilder('c')
            ->select($selectFields)
            ->join(LanguageConstantTranslation::class, 'ct', Join::WITH, 'ct.translatable = c AND ct.locale = :locale')
            ->setParameter('locale', $locale);
    }
}
