<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;

class AdministratorGridFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface;
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridService
     */
    protected $administratorGridService;

    public function __construct(EntityManagerInterface $em, AdministratorGridService $administratorGridService)
    {
        $this->em = $em;
        $this->administratorGridService = $administratorGridService;
    }

    public function restoreAndRememberGridLimit(Administrator $administrator, Grid $grid)
    {
        $this->administratorGridService->restoreGridLimit($administrator, $grid);
        $gridLimit = $this->administratorGridService->rememberGridLimit($administrator, $grid);
        $this->em->persist($gridLimit);
        $this->em->flush();
    }
}
