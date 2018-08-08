<?php

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class StatisticsRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[]
     */
    public function getCustomersRegistrationsCountByDayBetweenTwoDateTimes(DateTime $start, DateTime $end)
    {
        $resultSetMapping = new ResultSetMapping();
        $resultSetMapping->addScalarResult('count', 'count');
        $resultSetMapping->addScalarResult('date', 'date', Type::DATE);

        $query = $this->em->createNativeQuery(
            'SELECT DATE(u.created_at) AS date, COUNT(u.created_at) AS count
            FROM users u
            WHERE u.created_at BETWEEN :start_date AND :end_date
            GROUP BY date
            ORDER BY date ASC',
            $resultSetMapping
        );

        $query->setParameter('start_date', $start);
        $query->setParameter('end_date', $end);

        return array_map(
            function (array $item) {
                return new ValueByDateTimeDataPoint($item['count'], $item['date']);
            },
            $query->getResult()
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[]
     */
    public function getNewOrdersCountByDayBetweenTwoDateTimes(DateTime $start, DateTime $end)
    {
        $resultSetMapping = new ResultSetMapping();
        $resultSetMapping->addScalarResult('count', 'count');
        $resultSetMapping->addScalarResult('date', 'date', Type::DATE);

        $query = $this->em->createNativeQuery(
            'SELECT DATE(o.created_at) AS date, COUNT(o.created_at) AS count
            FROM orders o
            WHERE o.created_at BETWEEN :start_date AND :end_date
            GROUP BY date
            ORDER BY date ASC',
            $resultSetMapping
        );

        $query->setParameter('start_date', $start);
        $query->setParameter('end_date', $end);

        return array_map(
            function (array $item) {
                return new ValueByDateTimeDataPoint($item['count'], $item['date']);
            },
            $query->getResult()
        );
    }
}
