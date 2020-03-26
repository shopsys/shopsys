<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Parameter\Unit\ParameterUnitDataFactory;
use App\Model\Product\Parameter\Unit\ParameterUnitFacade;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class ParameterUnitDataFixture extends AbstractReferenceFixture
{
    private const LOCALES = ['cs', 'sk'];

    /**
     * @var \App\Model\Product\Parameter\Unit\ParameterUnitFacade
     */
    private $parameterUnitFacade;

    /**
     * @var \App\Model\Product\Parameter\Unit\ParameterUnitDataFactory
     */
    private $parameterUnitDataFactory;

    /**
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitFacade $parameterUnitFacade
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitDataFactory $parameterUnitDataFactory
     */
    public function __construct(
        ParameterUnitFacade $parameterUnitFacade,
        ParameterUnitDataFactory $parameterUnitDataFactory
    ) {
        $this->parameterUnitFacade = $parameterUnitFacade;
        $this->parameterUnitDataFactory = $parameterUnitDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $units = [
            'GRAM' => 'g',
            'CENTIMETER' => 'cm',
            'TON' => 't',
            'KILOWATT' => 'kW',
            'KILOGRAM' => 'kg',
            'WATT' => 'W',
            'VOLT' => 'V',
            'METER' => 'm',
        ];

        foreach ($units as $unit => $name) {
            $this->saveParameterUnit($unit, $name);
        }
    }

    /**
     * @param string $unit
     * @param string $name
     */
    private function saveParameterUnit(string $unit, string $name): void
    {
        $parameterUnitData = $this->parameterUnitDataFactory->create();
        $parameterUnitData->unit = $unit;
        foreach (self::LOCALES as $locale) {
            $parameterUnitData->name[$locale] = $name;
        }

        $this->parameterUnitFacade->create($parameterUnitData);
    }
}
