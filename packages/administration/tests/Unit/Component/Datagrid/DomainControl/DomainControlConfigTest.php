<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\DomainControl;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use stdClass;

final class DomainControlConfigTest extends TestCase
{
    public function testDomainEntityEnablesTheFilterByDefault(): void
    {
        $domainControlConfig = (new CrudConfig('Product review', null, TestConfiguredDomainEntity::class, 'ProductReviewController'))
            ->getConfig()
            ->getDomainControlConfig();

        $this->assertSame(DomainControlType::FILTER, $domainControlConfig->type);
        $this->assertTrue($domainControlConfig->isEnabled());
        $this->assertNull($domainControlConfig->allowedDomainIds);
        $this->assertSame('crud_product_review', $domainControlConfig->filterNamespace);
    }

    public function testEntityWithoutDomainHasNoDomainControlByDefault(): void
    {
        $domainControlConfig = (new CrudConfig('Product review', null, stdClass::class))->getConfig()->getDomainControlConfig();

        $this->assertSame(DomainControlType::NONE, $domainControlConfig->type);
        $this->assertFalse($domainControlConfig->isEnabled());
    }

    public function testDomainControlIsDisabledForUnknownEntityClass(): void
    {
        $domainControlConfig = (new CrudConfig('Product review'))->getConfig()->getDomainControlConfig();

        $this->assertSame(DomainControlType::NONE, $domainControlConfig->type);
    }

    public function testFilterConfigurationIsStored(): void
    {
        $crudConfig = new CrudConfig('Product review', null, null, 'ProductReviewController');

        $crudConfig->setListDomainControl(DomainControlType::FILTER, [1, 3], 'crud_shared');

        $domainControlConfig = $crudConfig->getConfig()->getDomainControlConfig();

        $this->assertSame(DomainControlType::FILTER, $domainControlConfig->type);
        $this->assertSame([1, 3], $domainControlConfig->allowedDomainIds);
        $this->assertSame('crud_shared', $domainControlConfig->filterNamespace);
    }

    public function testSwitcherConfigurationIsStored(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $crudConfig->setListDomainControl(DomainControlType::SWITCHER);

        $domainControlConfig = $crudConfig->getConfig()->getDomainControlConfig();

        $this->assertSame(DomainControlType::SWITCHER, $domainControlConfig->type);
        $this->assertNull($domainControlConfig->allowedDomainIds);
    }

    public function testDefaultFilterCanBeDisabled(): void
    {
        $crudConfig = new CrudConfig('Product review', null, TestConfiguredDomainEntity::class, 'ProductReviewController');

        $crudConfig->setListDomainControl(DomainControlType::NONE);

        $this->assertSame(DomainControlType::NONE, $crudConfig->getConfig()->getDomainControlConfig()->type);
    }

    public function testFilterNamespaceIsDerivedFromTheControllerWhenNotGiven(): void
    {
        $crudConfig = new CrudConfig('Product review', null, null, 'ProductReviewController');

        $crudConfig->setListDomainControl(DomainControlType::FILTER);

        $this->assertSame('crud_product_review', $crudConfig->getConfig()->getDomainControlConfig()->filterNamespace);
    }

    public function testFilterWithoutNamespaceAndControllerThrowsException(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The domain filter of a CRUD list requires the controller name to derive its namespace from.');

        $crudConfig->setListDomainControl(DomainControlType::FILTER);
    }

    public function testFilterWithoutNamespaceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The domain filter requires the namespace it remembers its selection under.');

        new DomainControlConfig(DomainControlType::FILTER);
    }

    public function testSwitcherWithAllowedDomainIdsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Allowed domain IDs are only supported by the domain filter.');

        new DomainControlConfig(DomainControlType::SWITCHER, [1, 3]);
    }

    public function testSwitcherWithFilterNamespaceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter namespace is only supported by the domain filter.');

        new DomainControlConfig(DomainControlType::SWITCHER, null, 'crud_shared');
    }

    public function testDisabledDomainControlWithAllowedDomainIdsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Allowed domain IDs are only supported by the domain filter.');

        new DomainControlConfig(DomainControlType::NONE, [1, 3]);
    }
}

final class TestConfiguredDomainEntity implements DomainSeparatedEntityInterface
{
    #[Override]
    public function getDomainId(): int
    {
        return 1;
    }
}
