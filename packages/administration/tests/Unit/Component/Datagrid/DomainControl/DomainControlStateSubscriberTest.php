<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\DomainControl;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScopeFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlStateSubscriber;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateEvent;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DomainControlStateSubscriberTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);

        Translator::injectSelf($translatorStub);
    }

    public function testScopeOfTheConfiguredDomainControlIsAdded(): void
    {
        $event = $this->dispatch($this->createConfig(new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test', 'domainId')), 2);

        $scope = $event->getParts()[DomainControlScope::class] ?? null;
        $this->assertInstanceOf(DomainControlScope::class, $scope);
        $this->assertSame(2, $scope->selectedDomainId);
        $this->assertEquals(Condition::in('domainId', [2]), $scope->createCondition());
    }

    public function testNothingIsAddedWithoutDomainControl(): void
    {
        $event = $this->dispatch($this->createConfig(null), null);

        $this->assertSame([], $event->getParts());
        $this->assertSame([], $event->getAdditionalFields());
    }

    /**
     * The administrator works with several domains at once, so the domain of every record is displayed.
     */
    public function testDomainIsDisplayedWhenAllDomainsAreSelected(): void
    {
        $event = $this->dispatch($this->createConfig(new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test', 'domainId')), null);

        $fields = $event->getAdditionalFields();
        $this->assertCount(1, $fields);
        $this->assertSame('domainId', $fields[0]->getName());
        $this->assertSame('@ShopsysAdministration/datagrid/cell/domain_icon.html.twig', $fields[0]->getTemplate());
    }

    /**
     * A record related to several domains has no single domain to display.
     */
    public function testDomainOfRecordRelatedToSeveralDomainsIsNotDisplayed(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), [
            'domains.domainId' => new PathDescription('domains.domainId', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::INTEGER),
        ]);

        $event = $this->dispatch($this->createConfig(new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test', 'domains.domainId'), $adapter), null);

        $this->assertArrayHasKey(DomainControlScope::class, $event->getParts());
        $this->assertSame([], $event->getAdditionalFields());
    }

    private function dispatch(DatagridConfig $config, ?int $selectedDomainId): DatagridStateEvent
    {
        $adminDomainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $adminDomainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn($selectedDomainId);
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getAdminEnabledDomainIds')->willReturn([1, 2, 3]);

        $subscriber = new DomainControlStateSubscriber(
            new DomainControlScopeFactory($adminDomainFilterTabsFacadeStub, $this->createStub(AdminDomainTabsFacade::class), $domainStub),
        );
        $event = new DatagridStateEvent($config, null);
        $subscriber->onDatagridState($event);

        return $event;
    }

    private function createConfig(
        ?DomainControlConfig $domainControlConfig,
        ?CapturingAdapter $adapter = null,
    ): DatagridConfig {
        return new DatagridConfig(
            'Product',
            $adapter ?? new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            [],
            new FilterCollection(),
            $domainControlConfig,
        );
    }
}
