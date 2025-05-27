<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\AiModel;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
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
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel|null
     */
    public function findById(int $id): ?AiModel
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param int $id
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel
     */
    public function getById(int $id): AiModel
    {
        $aiModel = $this->findById($id);

        if ($aiModel === null) {
            throw new InvalidArgumentException('AI model with id ' . $id . ' does not exist.');
        }

        return $aiModel;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllAiModelsQueryBuilder(): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('m')
            ->orderBy('m.name');
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel[]
     */
    public function getAllAiModels(): array
    {
        return $this->getAllAiModelsQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel[] $aiModels
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel[]
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
     * @param string $name
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel|null
     */
    public function findAiModelByName(string $name): ?AiModel
    {
        return $this->getRepository()->findOneBy(['name' => $name]);
    }
}
