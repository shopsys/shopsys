<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Flag;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFactory;

class FlagFacadeTest extends TestCase
{
    public function testCreate()
    {
        $flagFactory = new FlagFactory(new EntityNameResolver([]));

        $flagDataOriginal = new FlagData();
        $flagDataOriginal->name = ['cs' => 'flagNameCs', 'en' => 'flagNameEn'];
        $flagDataOriginal->rgbColor = '#336699';
        $flagDataOriginal->uuid = '862e7a33-c022-4ea5-b700-0e580ffbc6d0';
        $flag = $flagFactory->create($flagDataOriginal);

        $domain = $this->createMock(Domain::class);
        $flagDataFactory = new FlagDataFactory($domain);
        $flagDataNew = $flagDataFactory->createFromFlag($flag);

        $this->assertEquals($flagDataOriginal, $flagDataNew);
    }

    public function testEdit()
    {
        $flagDataOld = new FlagData();
        $flagDataOld->name = ['cs' => 'flagNameCs', 'en' => 'flagNameEn'];
        $flagDataOld->rgbColor = '#336699';
        $flagDataOld->uuid = '862e7a33-c022-4ea5-b700-0e580ffbc6d0';
        $flagDataEdit = new FlagData();
        $flagDataEdit->name = ['cs' => 'editFlagNameCs', 'en' => 'editFlagNameEn'];
        $flagDataEdit->rgbColor = '#00CCFF';
        $flagDataEdit->uuid = '862e7a33-c022-4ea5-b700-0e580ffbc6d0';
        $flag = new Flag($flagDataOld);

        $flag->edit($flagDataEdit);

        $domain = $this->createMock(Domain::class);
        $flagDataFactory = new FlagDataFactory($domain);
        $flagDataNew = $flagDataFactory->createFromFlag($flag);

        $this->assertEquals($flagDataEdit, $flagDataNew);
    }
}
