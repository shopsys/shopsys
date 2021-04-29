<?php

declare(strict_types=1);

namespace App\Model\Transport\Type;

use App\Model\Transport\Type\Exception\TransportTypeNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;

class TransportTypeRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
     * @return \App\Model\Transport\Type\TransportType
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
