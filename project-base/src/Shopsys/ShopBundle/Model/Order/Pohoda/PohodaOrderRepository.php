<?php

namespace Shopsys\ShopBundle\Model\Order\Pohoda;

use Doctrine\ORM\Query\ResultSetMapping;
use Shopsys\PohodaBundle\Component\Doctrine\PohodaEntityManager;

class PohodaOrderRepository
{
    const POHODA_ORDER_TYPE_FROM_ESHOP = 1;

    /**
     * @var \Shopsys\PohodaBundle\Component\Doctrine\PohodaEntityManager
     */
    private $pohodaEntityManager;

    /**
     * @param \Shopsys\PohodaBundle\Component\Doctrine\PohodaEntityManager $pohodaEntityManager
     */
    public function __construct(PohodaEntityManager $pohodaEntityManager)
    {
        $this->pohodaEntityManager = $pohodaEntityManager;
    }

    /**
     * @param $orderNumber
     * @return mixed
     */
    public function getOrderDataByOrderNumber($orderNumber)
    {
        $resultSetMapping = new ResultSetMapping();
        $resultSetMapping->addScalarResult('ID', 'orderIdExt')
            ->addScalarResult('Cislo', 'orderNumber')
            ->addScalarResult('Vyrizeno', 'isFinished')
            ->addScalarResult('DatStorn', 'stornoDateTime')
            ->addScalarResult('DatSave', 'lastUpdateDateTime')
            ->addScalarResult('RelTpObj', 'orderStatus');

        $query = $this->pohodaEntityManager->createNativeQuery(
            'SELECT ID, Cislo, PDoklad, Vyrizeno, DatStorn, DatSave, RelTpObj FROM OBJ WHERE Cislo = :orderNumber',
            $resultSetMapping
        )
            ->setParameters([
                'orderNumber' => $orderNumber,
            ]);

        return $query->getResult();
    }
}
