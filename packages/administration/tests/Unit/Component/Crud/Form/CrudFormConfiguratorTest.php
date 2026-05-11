<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud\Form;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormMode;
use Shopsys\AdministrationBundle\Component\Crud\Form\Exception\CrudFormAlreadyConfiguredException;
use Shopsys\AdministrationBundle\Component\Crud\Form\Exception\CrudFormNotConfiguredException;
use stdClass;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

final class CrudFormConfiguratorTest extends TestCase
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface|\PHPUnit\Framework\MockObject\Stub
     */
    private FormFactoryInterface $formFactoryStub;

    private stdClass $data;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->formFactoryStub = $this->createStub(FormFactoryInterface::class);
        $this->data = new stdClass();
    }

    public function testGetActionTypeReturnsValueFromConstructor(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);

        $this->assertSame(ActionType::EDIT, $configurator->getActionType());
    }

    public function testInitialModeIsUnconfigured(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::CREATE);

        $this->assertSame(CrudFormMode::UNCONFIGURED, $configurator->getMode());
    }

    public function testUseFormTypeSwitchesModeToFormType(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);

        $configurator->useFormType(FormTypeInterface::class);

        $this->assertSame(CrudFormMode::FORM_TYPE, $configurator->getMode());
    }

    public function testUseBuilderSwitchesModeToBuilder(): void
    {
        /** @var \Symfony\Component\Form\FormBuilderInterface|\PHPUnit\Framework\MockObject\Stub $builderStub */
        $builderStub = $this->createStub(FormBuilderInterface::class);
        $this->formFactoryStub->method('createBuilder')->willReturn($builderStub);

        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::CREATE);

        $configurator->useBuilder();

        $this->assertSame(CrudFormMode::BUILDER, $configurator->getMode());
    }

    public function testUseFormTypeCalledTwiceThrowsException(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);
        $configurator->useFormType(FormTypeInterface::class);

        $this->expectException(CrudFormAlreadyConfiguredException::class);

        $configurator->useFormType(FormTypeInterface::class);
    }

    public function testUseBuilderAfterUseFormTypeThrowsException(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);
        $configurator->useFormType(FormTypeInterface::class);

        $this->expectException(CrudFormAlreadyConfiguredException::class);

        $configurator->useBuilder();
    }

    public function testUseFormTypeAfterUseBuilderThrowsException(): void
    {
        /** @var \Symfony\Component\Form\FormBuilderInterface|\PHPUnit\Framework\MockObject\Stub $builderStub */
        $builderStub = $this->createStub(FormBuilderInterface::class);
        $this->formFactoryStub->method('createBuilder')->willReturn($builderStub);

        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::CREATE);
        $configurator->useBuilder();

        $this->expectException(CrudFormAlreadyConfiguredException::class);

        $configurator->useFormType(FormTypeInterface::class);
    }

    public function testUseBuilderReturnsSameInstanceOnMultipleCalls(): void
    {
        /** @var \Symfony\Component\Form\FormBuilderInterface|\PHPUnit\Framework\MockObject\Stub $builderStub */
        $builderStub = $this->createStub(FormBuilderInterface::class);
        $this->formFactoryStub->method('createBuilder')->willReturn($builderStub);

        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::CREATE);

        $first = $configurator->useBuilder();
        $second = $configurator->useBuilder();

        $this->assertSame($first, $second);
    }

    public function testSetFormOptionStoresValueRetrievableByGetFormOption(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);

        $configurator->setFormOption('attr', ['class' => 'test']);

        $this->assertSame(['class' => 'test'], $configurator->getFormOption('attr'));
    }

    public function testSetFormOptionReturnsSelfForFluentInterface(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);

        $result = $configurator->setFormOption('key', 'value');

        $this->assertSame($configurator, $result);
    }

    public function testSetFormOptionAfterBuilderCreatedThrowsException(): void
    {
        /** @var \Symfony\Component\Form\FormBuilderInterface|\PHPUnit\Framework\MockObject\Stub $builderStub */
        $builderStub = $this->createStub(FormBuilderInterface::class);
        $this->formFactoryStub->method('createBuilder')->willReturn($builderStub);

        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::CREATE);
        $configurator->useBuilder();

        $this->expectException(CrudFormAlreadyConfiguredException::class);

        $configurator->setFormOption('key', 'value');
    }

    public function testGetFormOptionPrioritizesFormOptionsOverFormTypeOptions(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);
        $configurator->useFormType(FormTypeInterface::class, ['scenario' => 'original']);
        $configurator->setFormOption('scenario', 'overridden');

        $this->assertSame('overridden', $configurator->getFormOption('scenario'));
    }

    public function testGetFormOptionFallsBackToFormTypeOptions(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);
        $configurator->useFormType(FormTypeInterface::class, ['scenario' => 'edit']);

        $this->assertSame('edit', $configurator->getFormOption('scenario'));
    }

    public function testGetFormOptionReturnsDefaultWhenNotFound(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);

        $this->assertSame('fallback', $configurator->getFormOption('nonexistent', 'fallback'));
    }

    public function testGetFormOptionReturnsNullByDefault(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);

        $this->assertNull($configurator->getFormOption('nonexistent'));
    }

    public function testBuildFormWhenUnconfiguredThrowsException(): void
    {
        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::EDIT);

        $this->expectException(CrudFormNotConfiguredException::class);

        $configurator->buildForm();
    }

    public function testBuildFormInFormTypeModeCallsFactoryWithMergedOptions(): void
    {
        $formStub = $this->createStub(FormInterface::class);
        $formFactoryMock = $this->createMock(FormFactoryInterface::class);
        $formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                FormTypeInterface::class,
                $this->data,
                ['entity' => 'test', 'custom' => 'override'],
            )
            ->willReturn($formStub);

        $configurator = new CrudFormConfigurator($formFactoryMock, $this->data, ActionType::EDIT);
        $configurator->useFormType(FormTypeInterface::class, ['entity' => 'test', 'custom' => 'original']);
        $configurator->setFormOption('custom', 'override');

        $result = $configurator->buildForm();

        $this->assertSame($formStub, $result);
    }

    public function testBuildFormInBuilderModeReturnsFormFromBuilder(): void
    {
        $formStub = $this->createStub(FormInterface::class);
        /** @var \Symfony\Component\Form\FormBuilderInterface|\PHPUnit\Framework\MockObject\Stub $builderStub */
        $builderStub = $this->createStub(FormBuilderInterface::class);
        $builderStub->method('getForm')->willReturn($formStub);
        $this->formFactoryStub->method('createBuilder')->willReturn($builderStub);

        $configurator = new CrudFormConfigurator($this->formFactoryStub, $this->data, ActionType::CREATE);
        $configurator->useBuilder();

        $result = $configurator->buildForm();

        $this->assertSame($formStub, $result);
    }

    public function testSetFormOptionBeforeUseFormTypeIsAllowed(): void
    {
        $formStub = $this->createStub(FormInterface::class);
        $formFactoryMock = $this->createMock(FormFactoryInterface::class);
        $formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                FormTypeInterface::class,
                $this->data,
                ['pre_option' => 'value'],
            )
            ->willReturn($formStub);

        $configurator = new CrudFormConfigurator($formFactoryMock, $this->data, ActionType::CREATE);
        $configurator->setFormOption('pre_option', 'value');
        $configurator->useFormType(FormTypeInterface::class);

        $configurator->buildForm();
    }

    public function testSetFormOptionAfterUseFormTypeIsAllowed(): void
    {
        $formStub = $this->createStub(FormInterface::class);
        $formFactoryMock = $this->createMock(FormFactoryInterface::class);
        $formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                FormTypeInterface::class,
                $this->data,
                ['post_option' => 'value'],
            )
            ->willReturn($formStub);

        $configurator = new CrudFormConfigurator($formFactoryMock, $this->data, ActionType::EDIT);
        $configurator->useFormType(FormTypeInterface::class);
        $configurator->setFormOption('post_option', 'value');

        $configurator->buildForm();
    }
}
