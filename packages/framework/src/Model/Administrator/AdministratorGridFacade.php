<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\InvalidGridLimitValueException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\RememberGridLimitException;

class AdministratorGridFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface;
     */
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimitFactoryInterface $administratorGridLimitFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        protected readonly AdministratorGridLimitFactoryInterface $administratorGridLimitFactory,
    ) {
        $this->em = $em;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param \Shopsys\FrameworkBundle\Component\Grid\Grid $grid
     */
    public function restoreAndRememberGridLimit(Administrator $administrator, Grid $grid)
    {
        $administrator->restoreGridLimit($grid);
        $this->rememberGridLimit($administrator, $grid);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param \Shopsys\FrameworkBundle\Component\Grid\Grid $grid
     */
    protected function rememberGridLimit(Administrator $administrator, Grid $grid)
    {
        if (!$grid->isEnabledPaging()) {
            throw new RememberGridLimitException($grid->getId());
        }

        if ($grid->getLimit() <= 0) {
            throw new InvalidGridLimitValueException($grid->getLimit());
        }

        $gridLimit = $administrator->getGridLimit($grid->getId());

        if ($gridLimit === null) {
            $gridLimit = $this->administratorGridLimitFactory->create(
                $administrator,
                $grid->getId(),
                $grid->getLimit(),
            );
            $administrator->addGridLimit($gridLimit);
        } else {
            $gridLimit->setLimit($grid->getLimit());
        }
    }
}
