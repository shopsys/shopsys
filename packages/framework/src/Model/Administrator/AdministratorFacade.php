<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdministratorFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository
     */
    protected $administratorRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFactoryInterface
     */
    protected $administratorFactory;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository $administratorRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFactoryInterface $administratorFactory
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $em,
        AdministratorRepository $administratorRepository,
        AdministratorFactoryInterface $administratorFactory,
        EncoderFactoryInterface $encoderFactory,
        TokenStorageInterface $tokenStorage
    ) {
        $this->administratorRepository = $administratorRepository;
        $this->em = $em;
        $this->administratorFactory = $administratorFactory;
        $this->encoderFactory = $encoderFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function create(AdministratorData $administratorData)
    {
        $administratorByUserName = $this->administratorRepository->findByUserName($administratorData->username);
        if ($administratorByUserName !== null) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DuplicateUserNameException($administratorByUserName->getUsername());
        }
        $administrator = $this->administratorFactory->create($administratorData);
        $administrator->setPassword($administratorData->password, $this->encoderFactory);

        $this->em->persist($administrator);
        $this->em->flush();

        return $administrator;
    }

    /**
     * @param int $administratorId
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function edit($administratorId, AdministratorData $administratorData)
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $administratorByUserName = $this->administratorRepository->findByUserName($administratorData->username);
        $administrator->edit(
            $administratorData,
            $this->encoderFactory,
            $administratorByUserName
        );

        $this->em->flush();

        return $administrator;
    }

    /**
     * @param int $administratorId
     */
    public function delete($administratorId)
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $adminCountExcludingSuperadmin = $this->administratorRepository->getCountExcludingSuperadmin();
        $administrator->checkForDelete($this->tokenStorage, $adminCountExcludingSuperadmin);
        $this->em->remove($administrator);
        $this->em->flush();
    }

    /**
     * @param string $administratorUsername
     * @param string $newPassword
     */
    public function changePassword($administratorUsername, $newPassword)
    {
        $administrator = $this->administratorRepository->getByUserName($administratorUsername);
        $administrator->setPassword($newPassword, $this->encoderFactory);
        $this->em->flush($administrator);
    }

    /**
     * @param int $administratorId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getById($administratorId)
    {
        return $this->administratorRepository->getById($administratorId);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllListableQueryBuilder()
    {
        return $this->administratorRepository->getAllListableQueryBuilder();
    }
}
