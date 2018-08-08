<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade;

class CustomerPasswordFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserRepository
     */
    protected $userRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade
     */
    protected $resetPasswordMailFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerPasswordService
     */
    protected $customerPasswordService;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        CustomerPasswordService $customerPasswordService,
        ResetPasswordMailFacade $resetPasswordMailFacade
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->customerPasswordService = $customerPasswordService;
        $this->resetPasswordMailFacade = $resetPasswordMailFacade;
    }

    public function resetPassword(string $email, int $domainId): void
    {
        $user = $this->userRepository->getUserByEmailAndDomain($email, $domainId);

        $this->customerPasswordService->resetPassword($user);
        $this->em->flush($user);
        $this->resetPasswordMailFacade->sendMail($user);
    }

    public function isResetPasswordHashValid(string $email, int $domainId, ?string $hash): bool
    {
        $user = $this->userRepository->getUserByEmailAndDomain($email, $domainId);

        return $this->customerPasswordService->isResetPasswordHashValid($user, $hash);
    }

    public function setNewPassword(string $email, int $domainId, ?string $hash, string $newPassword): \Shopsys\FrameworkBundle\Model\Customer\User
    {
        $user = $this->userRepository->getUserByEmailAndDomain($email, $domainId);

        $this->customerPasswordService->setNewPassword($user, $hash, $newPassword);

        return $user;
    }
}
