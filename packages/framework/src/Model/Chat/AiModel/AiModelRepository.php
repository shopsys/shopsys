<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\AiModel;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;

class AiModelRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(AiModel::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel|null
     */
    public function getModelById(int $id): ?AiModel
    {
        return $this->getRepository()
            ->createQueryBuilder('m')
            ->where('m.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllAiModelsQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('m')
            ->orderBy('m.name');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel[]
     */
    public function getAllAiModels(): array {
        return $this->getAllAiModelsQueryBuilder()
            ->orderBy('m.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param AiModel[] $aiModels
     * @return AiModel[]
     */
    public function saveAiModels(array $aiModels): array
    {
        foreach ($aiModels as $aiModel) {
            if (!$aiModel instanceof AiModel) {
                throw new InvalidArgumentException('Expected instance of AiModel');
            }
            $this->em->persist($aiModel);
        }
        $this->em->flush();

        return $aiModels;
    }

    /**
     * @param int $id
     * @return boolean
     */
    public function delete(int $id): bool
    {
        $aiModel = $this->getModelById($id);
        if ($aiModel === null) {
            throw new \InvalidArgumentException('AI model with id ' . $id . ' does not exist.');
        }

        $this->em->remove($aiModel);
        $this->em->flush();

        return true;
    }
}
