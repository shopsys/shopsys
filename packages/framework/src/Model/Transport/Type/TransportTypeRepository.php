<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use Shopsys\FrameworkBundle\Model\Transport\Type\Exception\TransportTypeNotFoundException;

class TransportTypeRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\Persistence\ObjectRepository
     */
    protected function getTransportTypeRepository(): ObjectRepository
    {
        return $this->em->getRepository(TransportType::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType
     */
    public function getById(int $id): TransportType
    {
        $transportType = $this->getTransportTypeRepository()->find($id);

        if ($transportType === null) {
            $message = sprintf('Transport type with ID "%d" not found.', $id);

            throw new TransportTypeNotFoundException($message);
        }

        return $transportType;
    }

    /**
     * @param string $code
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType
     */
    public function getByCode(string $code): TransportType
    {
        $transportType = $this->getTransportTypeRepository()->findOneBy(['code' => $code]);

        if ($transportType === null) {
            $message = sprintf('Transport type with code "%s" not found.', $code);

            throw new TransportTypeNotFoundException($message);
        }

        return $transportType;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType[]
     */
    public function getAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('tt, ttt')
            ->from(TransportType::class, 'tt')
            ->join('tt.translations', 'ttt')
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLocalisedQueryBuilder(string $locale): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('tt, ttt')
            ->from(TransportType::class, 'tt')
            ->join('tt.translations', 'ttt', Join::WITH, 'ttt.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('tt.id');
    }
}
