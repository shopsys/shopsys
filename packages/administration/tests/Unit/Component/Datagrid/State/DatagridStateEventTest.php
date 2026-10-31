<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\State;

use LogicException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateEvent;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Symfony\Component\HttpFoundation\Request;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DatagridStateEventTest extends TestCase
{
    public function testQueryOfTheFormIsRead(): void
    {
        $event = $this->createEvent(new Request([
            'ProductReview_search' => [
                'text' => 'hrnek',
            ],
            'g' => [
                'ProductReview' => [
                    'page' => '2',
                ],
            ],
        ]));

        $this->assertTrue($event->isInRequest());
        $this->assertSame(['text' => 'hrnek'], $event->getQuery('ProductReview_search'));
        $this->assertNull($event->getQuery('ProductReview_filter'));
    }

    public function testNothingIsReadOutsideRequest(): void
    {
        $event = $this->createEvent(null);

        $this->assertFalse($event->isInRequest());
        $this->assertNull($event->getQuery('ProductReview_search'));
    }

    /**
     * A feature is changed in a project by replacing its subscriber, a second one would silently compete with it.
     */
    public function testPartOfTheSameClassIsAddedOnlyOnce(): void
    {
        $event = $this->createEvent(null);
        $event->addPart(new QuickSearch('ProductReview_search', null, []));

        $this->expectException(LogicException::class);

        $event->addPart(new QuickSearch('ProductReview_search', 'hrnek', []));
    }

    public function testFieldOfTheSameNameIsAddedOnlyOnce(): void
    {
        $event = $this->createEvent(null);
        $event->addField(new FieldDescriptor('domainId'));

        $this->expectException(LogicException::class);

        $event->addField(new FieldDescriptor('domainId'));
    }

    private function createEvent(?Request $request): DatagridStateEvent
    {
        $config = new DatagridConfig('ProductReview', new CapturingAdapter($this->createStub(DataSourceInterface::class)), [], new FilterCollection());

        return new DatagridStateEvent($config, $request);
    }
}
