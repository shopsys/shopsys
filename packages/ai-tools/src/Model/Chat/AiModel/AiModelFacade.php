<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\AiModel;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class AiModelFacade
{
    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelRepository $aiModelRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelFactory $aiModelFactory
     */
    public function __construct(
        protected readonly AiModelRepository $aiModelRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly AiModelFactory $aiModelFactory,
    ) {
    }

    /**
     * @param int $id
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel|null
     */
    public function getById(int $id): ?AiModel
    {
        return $this->aiModelRepository->getById($id);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllAiModelsQueryBuilder(): QueryBuilder
    {
        return $this->aiModelRepository->getAllAiModelsQueryBuilder();
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel[]
     */
    public function getAllAiModels(): array
    {
        return $this->aiModelRepository->getAllAiModels();
    }

    /**
     * @param array $aiModels
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel[]
     */
    public function saveAiModels(array $aiModels): array
    {
        return $this->aiModelRepository->saveAiModels($aiModels);
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $aiModel = $this->getById($id);

        $this->em->remove($aiModel);
        $this->em->flush();
    }

    /**
     * @param int $id
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData $aiModelData
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel
     */
    public function edit(int $id, AiModelData $aiModelData): AiModel
    {
        $aiModel = $this->getById($id);
        $aiModel->edit($aiModelData);
        $this->em->flush();

        return $aiModel;
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData $aiModelData
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel
     */
    public function create(AiModelData $aiModelData): AiModel
    {
        $aiModel = $this->aiModelFactory->create($aiModelData);
        $this->em->persist($aiModel);
        $this->em->flush();

        return $aiModel;
    }

    /**
     * @param string $name
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel|null
     */
    public function findAiModelByName(string $name): ?AiModel
    {
        return $this->aiModelRepository->findAiModelByName($name);
    }
}
