<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class TransportGroupFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly TransportGroupRepository $transportGroupRepository,
        protected readonly TransportGroupFactory $transportGroupFactory,
        protected readonly ImageFacade $imageFacade,
    ) {
    }

    public function create(TransportGroupData $transportGroupData): TransportGroup
    {
        $transportGroup = $this->transportGroupFactory->create($transportGroupData);
        $this->em->persist($transportGroup);
        $this->em->flush();
        $this->imageFacade->manageImages($transportGroup, $transportGroupData->image);
        $this->em->flush();

        return $transportGroup;
    }

    public function edit(TransportGroup $transportGroup, TransportGroupData $transportGroupData): void
    {
        $transportGroup->edit($transportGroupData);
        $this->imageFacade->manageImages($transportGroup, $transportGroupData->image);

        $this->em->flush();
    }

    public function getById(int $transportGroupId): TransportGroup
    {
        return $this->transportGroupRepository->getById($transportGroupId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportGroup[]
     */
    public function getAll(): array
    {
        return $this->transportGroupRepository->getAll();
    }

    public function delete(TransportGroup $transportGroup): void
    {
        $this->em->remove($transportGroup);
        $this->em->flush();
    }
}
