<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;

class AdministratorFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository
     */
    protected $administratorRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorService
     */
    protected $administratorService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFactoryInterface
     */
    protected $administratorFactory;

    public function __construct(
        EntityManagerInterface $em,
        AdministratorRepository $administratorRepository,
        AdministratorService $administratorService,
        AdministratorFactoryInterface $administratorFactory
    ) {
        $this->administratorRepository = $administratorRepository;
        $this->administratorService = $administratorService;
        $this->em = $em;
        $this->administratorFactory = $administratorFactory;
    }

    public function create(AdministratorData $administratorData): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        $administratorByUserName = $this->administratorRepository->findByUserName($administratorData->username);
        if ($administratorByUserName !== null) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DuplicateUserNameException($administratorByUserName->getUsername());
        }
        $administrator = $this->administratorFactory->create($administratorData);
        $this->administratorService->setPassword($administrator, $administratorData->password);

        $this->em->persist($administrator);
        $this->em->flush();

        return $administrator;
    }
    
    public function edit(int $administratorId, AdministratorData $administratorData): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $administratorByUserName = $this->administratorRepository->findByUserName($administratorData->username);
        $administratorEdited = $this->administratorService->edit(
            $administratorData,
            $administrator,
            $administratorByUserName
        );

        $this->em->flush();

        return $administratorEdited;
    }
    
    public function delete(int $administratorId): void
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $adminCountExcludingSuperadmin = $this->administratorRepository->getCountExcludingSuperadmin();
        $this->administratorService->delete($administrator, $adminCountExcludingSuperadmin);
        $this->em->remove($administrator);
        $this->em->flush();
    }
    
    public function changePassword(string $administratorUsername, string $newPassword): void
    {
        $administrator = $this->administratorRepository->getByUserName($administratorUsername);
        $this->administratorService->setPassword($administrator, $newPassword);
        $this->em->flush($administrator);
    }
    
    public function getById(int $administratorId): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        return $this->administratorRepository->getById($administratorId);
    }

    public function getAllListableQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->administratorRepository->getAllListableQueryBuilder();
    }
}
