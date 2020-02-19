<?php

declare(strict_types=1);

namespace App\Model\Transfer;

class TransferFacade
{
    /**
     * @var \App\Model\Transfer\TransferRepository
     */
    private $transferRepository;

    /**
     * @param \App\Model\Transfer\TransferRepository $transferRepository
     */
    public function __construct(TransferRepository $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

    /**
     * @return \App\Model\Transfer\Transfer[]
     */
    public function getAll(): array
    {
        return $this->transferRepository->getAll();
    }
}
