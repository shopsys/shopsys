<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud\Template;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Template\CrudTemplateParameters;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;

final class CrudTemplateParametersTest extends TestCase
{
    public function testActionTypeIsExposed(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, []);

        $this->assertSame(ActionType::EDIT, $templateParameters->getActionType());
        $this->assertTrue($templateParameters->isAction(ActionType::EDIT));
        $this->assertTrue($templateParameters->isAction(ActionType::LIST, ActionType::EDIT));
        $this->assertFalse($templateParameters->isAction(ActionType::LIST, ActionType::CREATE));
    }

    public function testEntityIsReturnedNarrowedToGivenClass(): void
    {
        $entity = new TestPresentableEntity();
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, [], $entity);

        $this->assertTrue($templateParameters->hasEntity());
        $this->assertSame($entity, $templateParameters->getEntity(TestPresentableEntity::class));
    }

    public function testActionWithoutEntityHasNoEntity(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::LIST, []);

        $this->assertFalse($templateParameters->hasEntity());
    }

    public function testGettingMissingEntityThrowsException(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::LIST, []);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "list" action has no entity.');

        $templateParameters->getEntity(TestPresentableEntity::class);
    }

    public function testGettingEntityOfDifferentClassThrowsException(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, [], new TestPresentableEntity());

        $this->expectException(InvalidArgumentException::class);

        $templateParameters->getEntity(OtherTestPresentableEntity::class);
    }

    public function testSetParametersAreAddedToBaseParameters(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);

        $templateParameters
            ->set('gridView', 'controller grid')
            ->set('timeline', 'extension timeline');

        $this->assertSame([
            'title' => 'Product review',
            'gridView' => 'controller grid',
            'timeline' => 'extension timeline',
        ], $templateParameters->toArray());
    }

    public function testBaseAndSetParametersCanBeRead(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);
        $templateParameters->set('gridView', 'controller grid');

        $this->assertTrue($templateParameters->has('title'));
        $this->assertTrue($templateParameters->has('gridView'));
        $this->assertFalse($templateParameters->has('timeline'));
        $this->assertSame('Product review', $templateParameters->get('title'));
        $this->assertSame('controller grid', $templateParameters->get('gridView'));
    }

    public function testReadingMissingParameterThrowsException(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Template parameter "timeline" of the "edit" action is not set.');

        $templateParameters->get('timeline');
    }

    public function testSettingBaseParameterThrowsException(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['form' => 'Base form']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Template parameter "form" of the "edit" action is already set. Choose a different name.');

        $templateParameters->set('form', 'Custom form');
    }
}

final class TestPresentableEntity implements Presentable
{
    #[Override]
    public function toHumanReadable(): string
    {
        return 'Test entity';
    }
}

final class OtherTestPresentableEntity implements Presentable
{
    #[Override]
    public function toHumanReadable(): string
    {
        return 'Other test entity';
    }
}
