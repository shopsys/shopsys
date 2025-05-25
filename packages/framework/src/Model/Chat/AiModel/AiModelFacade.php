<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\AiModel;

use Doctrine\ORM\EntityManagerInterface;

class AiModelFacade
{
    /**
     * @param AiModelRepository $aiModelRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        protected readonly AiModelRepository $aiModelRepository,
        protected readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel|null
     */
    public function getModelById(int $id): ?AiModel
    {
        return $this->aiModelRepository->getModelById($id);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllAiModelsQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->aiModelRepository->getAllAiModelsQueryBuilder();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel[]
     */
    public function getAllAiModels(): array
    {
        return $this->aiModelRepository->getAllAiModels();
    }

    /**
     * @param array $aiModels
     * @return AiModel[]
     */
    public function saveAiModels(array $aiModels): array
    {
        return $this->aiModelRepository->saveAiModels($aiModels);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->aiModelRepository->delete($id);
    }

    /**
     * @param AiModelData $aiModelData
     * @return \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel
     */
    public function edit(int $id, AiModelData $aiModelData): AiModel
    {
        $aiModel = $this->getModelById($id);
        $aiModel->edit($aiModelData);
        $this->em->flush();

        return $aiModel;
    }

    /**
     * @param AiModelData $aiModelData
     * @return AiModel
     */
    public function create(AiModelData $aiModelData): AiModel
    {
        $aiModel = new AiModel($aiModelData);
        $this->em->persist($aiModel);
        $this->em->flush();

        return $aiModel;
    }
}
