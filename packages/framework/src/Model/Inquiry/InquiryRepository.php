<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Inquiry\Exception\InquiryNotFoundException;

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

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry
     */
    public function getById(int $id): Inquiry
    {
        $inquiry = $this->getInquiryRepository()->find($id);

        if ($inquiry === null) {
            throw new InquiryNotFoundException();
        }

        return $inquiry;
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getInquiriesQueryBuilder(string $locale): QueryBuilder
    {
        return $this->getInquiryRepository()
            ->createQueryBuilder('i')
            ->addSelect('IDENTITY(i.product) as productId')
            ->addSelect('pt.name as productName')
            ->addSelect('CONCAT(i.lastName, \' \', i.firstName) as fullName')
            ->addSelect('CONCAT(i.companyName, \' (\', i.companyNumber, \')\') as company')
            ->leftJoin('i.product', 'p')
            ->leftJoin('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('i.createdAt', 'DESC');
    }
}
