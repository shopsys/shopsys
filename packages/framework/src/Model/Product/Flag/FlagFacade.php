<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;

class FlagFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository
     */
    protected $flagRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagService
     */
    protected $flagService;

    public function __construct(
        EntityManagerInterface $em,
        FlagRepository $flagRepository,
        FlagService $flagService
    ) {
        $this->em = $em;
        $this->flagRepository = $flagRepository;
        $this->flagService = $flagService;
    }

    /**
     * @param int $flagId
     */
    public function getById($flagId): \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
    {
        return $this->flagRepository->getById($flagId);
    }

    public function create(FlagData $flagData): \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
    {
        $flag = $this->flagService->create($flagData);
        $this->em->persist($flag);
        $this->em->flush();

        return $flag;
    }

    /**
     * @param int $flagId
     */
    public function edit($flagId, FlagData $flagData): \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
    {
        $flag = $this->flagRepository->getById($flagId);
        $this->flagService->edit($flag, $flagData);
        $this->em->flush();

        return $flag;
    }

    /**
     * @param int $flagId
     */
    public function deleteById($flagId)
    {
        $flag = $this->flagRepository->getById($flagId);

        $this->em->remove($flag);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getAll(): array
    {
        return $this->flagRepository->getAll();
    }
}
