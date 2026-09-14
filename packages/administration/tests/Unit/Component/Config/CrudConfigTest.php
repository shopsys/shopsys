<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Config;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;

class CrudConfigTest extends TestCase
{
    public function testCustomActionsAreEnabledByDefault(): void
    {
        $config = new CrudConfig('Review', customActionNames: ['approve', 'reject'])->getConfig();

        $this->assertSame(['approve', 'reject'], $config->getEnabledCustomActionNames());
        $this->assertTrue($config->isActionEnabled('approve'));
        $this->assertFalse($config->isFullDisabled());
    }

    public function testCustomActionCanBeDisabledAndEnabledAgainByName(): void
    {
        $crudConfig = new CrudConfig('Review', customActionNames: ['approve', 'reject']);

        $crudConfig->disableAction(['approve', 'reject']);
        $crudConfig->enableAction('reject');
        $config = $crudConfig->getConfig();

        $this->assertSame(['reject'], $config->getEnabledCustomActionNames());
        $this->assertFalse($config->isActionEnabled('approve'));
        $this->assertTrue($config->isActionEnabled('reject'));
    }

    public function testBuiltInActionsCanBeReferencedByName(): void
    {
        $crudConfig = new CrudConfig('Review');

        $crudConfig->enableAction('detail');
        $crudConfig->disableAction('list');
        $config = $crudConfig->getConfig();

        $this->assertSame([ActionType::DETAIL], array_values($config->getActions()));
        $this->assertTrue($config->isActionEnabled('detail'));
        $this->assertFalse($config->isActionEnabled(ActionType::LIST));
    }

    public function testUnknownCustomActionNameIsRejected(): void
    {
        $crudConfig = new CrudConfig('Review', customActionNames: ['approve']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown custom action "publish" for "Review". Declared custom actions: approve.');

        $crudConfig->disableAction('publish');
    }

    public function testFullyDisabledControllerDisablesAllActions(): void
    {
        $crudConfig = new CrudConfig('Review', customActionNames: ['approve']);

        $crudConfig->disable(true);
        $config = $crudConfig->getConfig();

        $this->assertSame([], $config->getActions());
        $this->assertSame([], $config->getEnabledCustomActionNames());
        $this->assertFalse($config->isActionEnabled(ActionType::LIST));
        $this->assertFalse($config->isActionEnabled('approve'));
        $this->assertTrue($config->isFullDisabled());
    }

    public function testControllerWithOnlyCustomActionsIsNotConsideredDisabled(): void
    {
        $crudConfig = new CrudConfig('Review', customActionNames: ['approve']);

        $crudConfig->disableAction(ActionType::LIST);

        $this->assertFalse($crudConfig->getConfig()->isFullDisabled());

        $crudConfig->disableAction('approve');

        $this->assertTrue($crudConfig->getConfig()->isFullDisabled());
    }
}
