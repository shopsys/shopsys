<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Component\Grid\Grid;

class AdministratorGridService
{

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimitFactoryInterface
     */
    protected $administratorGridLimitFactory;

    public function __construct(AdministratorGridLimitFactoryInterface $administratorGridLimitFactory)
    {
        $this->administratorGridLimitFactory = $administratorGridLimitFactory;
    }

    public function rememberGridLimit(Administrator $administrator, Grid $grid): ?\Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit
    {
        if (!$grid->isEnabledPaging()) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\RememberGridLimitException($grid->getId());
        }
        if ($grid->getLimit() <= 0) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\InvalidGridLimitValueException($grid->getLimit());
        }

        $gridLimit = $administrator->getGridLimit($grid->getId());
        if ($gridLimit === null) {
            $gridLimit = $this->administratorGridLimitFactory->create($administrator, $grid->getId(), $grid->getLimit());
        } else {
            $gridLimit->setLimit($grid->getLimit());
        }

        return $gridLimit;
    }

    public function restoreGridLimit(Administrator $administrator, Grid $grid)
    {
        $customLimit = $administrator->getLimitByGridId($grid->getId());
        if ($customLimit !== null) {
            $grid->setDefaultLimit($customLimit);
        }
    }
}
