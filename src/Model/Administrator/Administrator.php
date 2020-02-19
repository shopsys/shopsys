<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator as BaseAdministrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData as BaseAdministratorData;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="administrators",
 *   indexes={
 *     @ORM\Index(columns={"username"})
 *   }
 * )
 */
class Administrator extends BaseAdministrator
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $transferIssuesLastSeenDateTime;

    /**
     * @param \App\Model\Administrator\AdministratorData $administratorData
     */
    public function __construct(BaseAdministratorData $administratorData)
    {
        parent::__construct($administratorData);
        $this->transferIssuesLastSeenDateTime = $administratorData->transferIssuesLastSeenDateTime;
    }

    /**
     * @param \App\Model\Administrator\AdministratorData $administratorData
     */
    public function edit(BaseAdministratorData $administratorData): void
    {
        parent::edit($administratorData);
    }

    /**
     * @return \DateTime
     */
    public function getTransferIssuesLastSeenDateTime(): DateTime
    {
        return $this->transferIssuesLastSeenDateTime;
    }

    /**
     * @param \DateTime $transferIssuesLastSeenDateTime
     */
    public function setTransferIssuesLastSeenDateTime(\DateTime $transferIssuesLastSeenDateTime): void
    {
        $this->transferIssuesLastSeenDateTime = $transferIssuesLastSeenDateTime;
    }
}
