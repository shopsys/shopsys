<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\ArrayAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestArrayGridController extends AdminBaseController
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory $datagridFactory
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\ArrayAdapterFactory $arrayAdapterFactory
     */
    public function __construct(
        private readonly DatagridFactory $datagridFactory,
        private readonly ArrayAdapterFactory $arrayAdapterFactory,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/array-grid')]
    public function listAction(): Response
    {
        $array = [
            ['id' => 1, 'name' => 'John', 'age' => 25],
            ['id' => 2, 'name' => 'Jane', 'age' => 30],
            ['id' => 3, 'name' => 'Doe', 'age' => 35],
            ['id' => 4, 'name' => 'Smith', 'age' => 40],
            ['id' => 5, 'name' => 'Brown', 'age' => 45],
            ['id' => 6, 'name' => 'Taylor', 'age' => 50],
            ['id' => 7, 'name' => 'Wilson', 'age' => 55],
            ['id' => 8, 'name' => 'Anderson', 'age' => 60],
            ['id' => 9, 'name' => 'Thomas', 'age' => 65],
            ['id' => 10, 'name' => 'Jackson', 'age' => 70],
            ['id' => 11, 'name' => 'White', 'age' => 75],
            ['id' => 12, 'name' => 'Harris', 'age' => 80],
            ['id' => 13, 'name' => 'Martin', 'age' => 85],
            ['id' => 14, 'name' => 'Thompson', 'age' => 90],
            ['id' => 15, 'name' => 'Garcia', 'age' => 95],
            ['id' => 16, 'name' => 'Martinez', 'age' => 100],
            ['id' => 17, 'name' => 'Robinson', 'age' => 105],
            ['id' => 18, 'name' => 'Clark', 'age' => 110],
            ['id' => 19, 'name' => 'Rodriguez', 'age' => 115],
            ['id' => 20, 'name' => 'Lewis', 'age' => 120],
            ['id' => 21, 'name' => 'Lee', 'age' => 125],
            ['id' => 22, 'name' => 'Walker', 'age' => 130],
            ['id' => 23, 'name' => 'Hall', 'age' => 135],
            ['id' => 24, 'name' => 'Allen', 'age' => 140],
            ['id' => 25, 'name' => 'Young', 'age' => 145],
            ['id' => 26, 'name' => 'Hernandez', 'age' => 150],
            ['id' => 27, 'name' => 'King', 'age' => 155],
            ['id' => 28, 'name' => 'Wright', 'age' => 160],
            ['id' => 29, 'name' => 'Lopez', 'age' => 165],
            ['id' => 30, 'name' => 'Hill', 'age' => 170],
            ['id' => 31, 'name' => 'Scott', 'age' => 175],
            ['id' => 32, 'name' => 'Green', 'age' => 180],
            ['id' => 33, 'name' => 'Adams', 'age' => 185],
            ['id' => 34, 'name' => 'Baker', 'age' => 190],
            ['id' => 35, 'name' => 'Gonzalez', 'age' => 195],
            ['id' => 36, 'name' => 'Nelson', 'age' => 200],
            ['id' => 37, 'name' => 'Carter', 'age' => 205],
            ['id' => 38, 'name' => 'Mitchell', 'age' => 210],
            ['id' => 39, 'name' => 'Perez', 'age' => 215],
            ['id' => 40, 'name' => 'Roberts', 'age' => 220],
            ['id' => 41, 'name' => 'Turner', 'age' => 225],
            ['id' => 42, 'name' => 'Phillips', 'age' => 230],
            ['id' => 43, 'name' => 'Campbell', 'age' => 235],
            ['id' => 44, 'name' => 'Parker', 'age' => 240],
            ['id' => 45, 'name' => 'Evans', 'age' => 245],
            ['id' => 46, 'name' => 'Edwards', 'age' => 250],
            ['id' => 47, 'name' => 'Collins', 'age' => 255],
            ['id' => 48, 'name' => 'Stewart', 'age' => 260],
            ['id' => 49, 'name' => 'Sanchez', 'age' => 265],
            ['id' => 50, 'name' => 'Morris', 'age' => 270],
        ];

        $datagrid = $this->datagridFactory->create($this->arrayAdapterFactory->create($array), [
            'name' => 'test-array',
        ]);

        $datagrid
            ->add('name', [
                'label' => 'Name',
                'sortable' => true,
            ])
            ->add('age', [
                'label' => 'Age',
                'help' => 'Test',
            ])
        ;

        return $this->render('Admin/test-array.html.twig', [
            'grid' => $datagrid->createView(),
        ]);
    }
}
