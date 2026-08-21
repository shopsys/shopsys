<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Config;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;

final class CrudConfigTemplateTest extends TestCase
{
    public function testDefaultTemplatesAreUsed(): void
    {
        $crudConfigData = (new CrudConfig('Product review'))->getConfig();

        $this->assertSame('@ShopsysAdministration/crud/list.html.twig', $crudConfigData->getTemplate(ActionType::LIST));
        $this->assertSame('@ShopsysAdministration/crud/detail.html.twig', $crudConfigData->getTemplate(ActionType::DETAIL));
        $this->assertSame('@ShopsysAdministration/crud/new.html.twig', $crudConfigData->getTemplate(ActionType::CREATE));
        $this->assertSame('@ShopsysAdministration/crud/edit.html.twig', $crudConfigData->getTemplate(ActionType::EDIT));
    }

    public function testTemplateOverrideIsStored(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $crudConfig->setTemplate(ActionType::EDIT, '@ShopsysAdministration/content/productReview/edit.html.twig');

        $crudConfigData = $crudConfig->getConfig();

        $this->assertSame('@ShopsysAdministration/content/productReview/edit.html.twig', $crudConfigData->getTemplate(ActionType::EDIT));
        $this->assertSame('@ShopsysAdministration/crud/list.html.twig', $crudConfigData->getTemplate(ActionType::LIST));
    }

    public function testTemplateOverrideCanBeReplaced(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $crudConfig->setTemplate(ActionType::LIST, '@ShopsysAdministration/content/productReview/list.html.twig');
        $crudConfig->setTemplate(ActionType::LIST, '@ShopsysAdministration/content/productReview/customList.html.twig');

        $this->assertSame(
            '@ShopsysAdministration/content/productReview/customList.html.twig',
            $crudConfig->getConfig()->getTemplate(ActionType::LIST),
        );
    }

    public function testSetTemplateForDeleteActionThrowsException(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "delete" action does not render a template.');

        $crudConfig->setTemplate(ActionType::DELETE, '@ShopsysAdministration/crud/delete.html.twig');
    }

    public function testGetTemplateForDeleteActionThrowsException(): void
    {
        $crudConfigData = (new CrudConfig('Product review'))->getConfig();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "delete" action does not render a template.');

        $crudConfigData->getTemplate(ActionType::DELETE);
    }
}
