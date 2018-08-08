<?php

namespace Tests\ShopBundle\Database\Form;

use Shopsys\FormTypesBundle\YesNoType;
use Tests\ShopBundle\Test\FunctionalTestCase;

class YesNoTypeTest extends FunctionalTestCase
{
    public function testGetDataReturnsTrue(): void
    {
        $form = $this->getForm();

        $form->setData(true);
        $this->assertSame(true, $form->getData());
    }

    public function testGetDataReturnsFalse(): void
    {
        $form = $this->getForm();

        $form->setData(false);
        $this->assertSame(false, $form->getData());
    }

    public function testGetDataReturnsTrueAfterSubmit(): void
    {
        $form = $this->getForm();

        $form->submit('1');
        $this->assertSame(true, $form->getData());
    }

    public function testGetDataReturnsFalseAfterSubmit(): void
    {
        $form = $this->getForm();

        $form->submit('0');
        $this->assertSame(false, $form->getData());
    }

    private function getForm(): \Symfony\Component\Form\FormInterface
    {
        $formFactory = $this->getContainer()->get('form.factory');
        /* @var $formFactory \Symfony\Component\Form\FormFactoryInterface */

        return $formFactory->create(YesNoType::class);
    }
}
