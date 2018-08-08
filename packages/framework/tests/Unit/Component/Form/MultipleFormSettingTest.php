<?php

namespace Tests\FrameworkBundle\Unit\Component\Form;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Form\MultipleFormSetting;

class MultipleFormSettingTest extends TestCase
{
    public function testCurrentFormIsMultiple(): void
    {
        $multipleFormSetting = new MultipleFormSetting();
        $multipleFormSetting->currentFormIsMultiple();

        $this->assertTrue($multipleFormSetting->isCurrentFormMultiple());
    }

    public function testCurrentFormIsNotMultiple(): void
    {
        $multipleFormSetting = new MultipleFormSetting();
        $multipleFormSetting->currentFormIsNotMultiple();

        $this->assertFalse($multipleFormSetting->isCurrentFormMultiple());
    }

    public function testDefaultValue(): void
    {
        $multipleFormSetting = new MultipleFormSetting();

        $this->assertSame(MultipleFormSetting::DEFAULT_MULTIPLE, $multipleFormSetting->isCurrentFormMultiple());
    }

    public function testReset(): void
    {
        $multipleFormSetting = new MultipleFormSetting();

        $multipleFormSetting->reset();
        $this->assertSame(MultipleFormSetting::DEFAULT_MULTIPLE, $multipleFormSetting->isCurrentFormMultiple());

        $multipleFormSetting->currentFormIsMultiple();
        $multipleFormSetting->reset();
        $this->assertSame(MultipleFormSetting::DEFAULT_MULTIPLE, $multipleFormSetting->isCurrentFormMultiple());

        $multipleFormSetting->currentFormIsNotMultiple();
        $multipleFormSetting->reset();
        $this->assertSame(MultipleFormSetting::DEFAULT_MULTIPLE, $multipleFormSetting->isCurrentFormMultiple());
    }
}
