<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\DomainControl;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
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
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class DomainControlStateSubscriberTest extends TestCase
{
    use SetTranslatorTrait;

    #[Override]
    protected function setUp(): void
    {
        $this->setTranslator();
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
        $event = $this->dispatch($this->createConfig(new DomainControlConfig()), null);

        $this->assertSame([], $event->getParts());
        $this->assertSame([], $event->getAdditionalFields());
    }

    public function testDomainIsDisplayedWhenAllDomainsAreSelected(): void
    {
        $event = $this->dispatch($this->createConfig(new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test', 'domainId')), null);

        $fields = $event->getAdditionalFields();
        $this->assertCount(1, $fields);
        $this->assertSame('domainId', $fields[0]->getName());
        $this->assertSame('@ShopsysAdministration/datagrid/cell/domain_icon.html.twig', $fields[0]->getTemplate());
    }

    #[DataProvider('provideDomainControlsDecidingWhetherTheDomainIsDisplayed')]
    public function testDomainIsDisplayedOnlyWhenWorthDisplaying(
        DomainControlConfig $domainControlConfig,
        int $selectedDomainId,
        bool $isDomainDisplayed,
    ): void {
        $event = $this->dispatch($this->createConfig($domainControlConfig), $selectedDomainId);

        $this->assertArrayHasKey(DomainControlScope::class, $event->getParts());
        $this->assertCount($isDomainDisplayed ? 1 : 0, $event->getAdditionalFields());
    }

    /**
     * @return iterable<string, array{\Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig, int, bool}>
     */
    public static function provideDomainControlsDecidingWhetherTheDomainIsDisplayed(): iterable
    {
        yield 'filter with one of several domains selected' => [new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test', 'domainId'), 2, true];

        yield 'filter of a single domain' => [new DomainControlConfig(DomainControlType::FILTER, [1], 'crud_test', 'domainId'), 1, false];

        yield 'switcher, which always selects one domain' => [new DomainControlConfig(DomainControlType::SWITCHER, null, null, 'domainId'), 2, false];

        yield 'entity without domain' => [new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test'), 2, false];
    }

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
        DomainControlConfig $domainControlConfig,
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
