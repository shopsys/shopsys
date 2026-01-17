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
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
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

    public function getByKey(string $key, string $namespace): LanguageConstant
    {
        $languageConstant = $this->findByKey($key, $namespace);

        if ($languageConstant === null) {
            throw new LanguageConstantNotFoundException(sprintf('Language constant with key "%s" and namespace "%s" not found', $key, $namespace));
        }

        return $languageConstant;
    }

    public function findByKey(string $key, string $namespace): ?LanguageConstant
    {
        return $this->getRepository()->findOneBy(['key' => $key, 'namespace' => $namespace]);
    }

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

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(LanguageConstant::class);
    }

    protected function getTranslationRepository(): EntityRepository
    {
        return $this->em->getRepository(LanguageConstantTranslation::class);
    }

    public function createNamespacedKey(string $namespace, string $key): string
    {
        return $namespace . '::' . $key;
    }

    protected function createTranslationQueryBuilder(string $locale, string $selectFields): QueryBuilder
    {
        return $this->getRepository()
            ->createQueryBuilder('c')
            ->select($selectFields)
            ->join(LanguageConstantTranslation::class, 'ct', Join::WITH, 'ct.translatable = c AND ct.locale = :locale')
            ->setParameter('locale', $locale);
    }
}
