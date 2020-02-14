<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class ProductTypeGridFactory implements GridFactoryInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    protected $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected $localization;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        EntityManagerInterface $em,
        GridFactory $gridFactory,
        Localization $localization
    ) {
        $this->em = $em;
        $this->gridFactory = $gridFactory;
        $this->localization = $localization;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('pt, ptt')
            ->from(ProductType::class, 'pt')
            ->join('pt.translations', 'ptt', Join::WITH, 'ptt.locale = :locale')
            ->orderBy('ptt.name')
            ->setParameter('locale', $this->localization->getAdminLocale());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pt.id');

        $grid = $this->gridFactory->create('productTypeList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'ptt.name', t('Name'), true);
        $grid->addColumn('akeneoCode', 'pt.akeneoCode', t('Akeneo kód'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_producttype_delete', ['id' => 'pt.id'])
            ->setConfirmMessage(t('Opravdu si přejete odstranit tento typ produktu?'));

        $grid->setTheme('Admin/Content/ProductType/listGrid.html.twig');

        return $grid;
    }
}
