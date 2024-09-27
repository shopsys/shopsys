<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Doctrine\ORM\EntityManagerInterface;

class InquiryFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryRepository $inquiryRepository
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryFactory $inquiryFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly InquiryRepository $inquiryRepository,
        protected readonly InquiryFactory $inquiryFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryData $inquiryData
     * @return \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry
     */
    public function create(InquiryData $inquiryData): Inquiry
    {
        $inquiry = $this->inquiryFactory->create($inquiryData);

        $this->em->persist($inquiry);
        $this->em->flush();

        return $inquiry;
    }
}
