<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class InquiryRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getInquiryRepository(): EntityRepository
    {
        return $this->em->getRepository(Inquiry::class);
    }
}
