<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\EntityLog\ChangeSet\Formatter;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\BooleanDataTypeFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\CollectionChangesFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\DateTimeDataTypeFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\MoneyDataTypeFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ResolvedChangesFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ScalarDataTypeFormatter;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;

class ResolvedChangesFormatterTest extends TestCase
{
    private ResolvedChangesFormatter $resolvedChangesFormatter;

    #[Override]
    protected function setUp(): void
    {
        $this->injectTranslatorStub();

        $dateTimeFormatterExtension = $this->createStub(DateTimeFormatterExtension::class);
        $dateTimeFormatterExtension
            ->method('formatDateTime')
            ->willReturnOnConsecutiveCalls('May 1, 2026, 10:00:00 AM', 'May 2, 2026, 11:00:00 AM');

        $this->resolvedChangesFormatter = new ResolvedChangesFormatter(
            new CollectionChangesFormatter(),
            new ScalarDataTypeFormatter(),
            new MoneyDataTypeFormatter(),
            new DateTimeDataTypeFormatter($dateTimeFormatterExtension),
            new BooleanDataTypeFormatter(),
        );
    }

    public function testFormatsChangedAttributesWithCodeElements(): void
    {
        $formattedChanges = $this->resolvedChangesFormatter->formatResolvedChanges([
            'name' => [
                'dataType' => 'string',
                'oldReadableValue' => 'Personal collection',
                'newReadableValue' => 'Packeta',
                'oldValue' => 'Personal collection',
                'newValue' => 'Packeta',
            ],
            'totalPriceWithVat' => [
                'dataType' => 'Money',
                'oldReadableValue' => '29.545',
                'newReadableValue' => '21.31',
                'oldValue' => null,
                'newValue' => null,
            ],
            'createdAt' => [
                'dataType' => 'DateTimeImmutable',
                'oldReadableValue' => null,
                'newReadableValue' => null,
                'oldValue' => '2026-05-01 10:00:00',
                'newValue' => '2026-05-02 11:00:00',
            ],
            'paid' => [
                'dataType' => 'boolean',
                'oldReadableValue' => false,
                'newReadableValue' => true,
                'oldValue' => false,
                'newValue' => true,
            ],
        ]);

        $this->assertSame(
            'Attribute <code>name</code> was changed from <code>Personal collection</code> to <code>Packeta</code>'
            . '<br>Attribute <code>totalPriceWithVat</code> was changed from <code>29.55</code> to <code>21.31</code>'
            . '<br>Attribute <code>createdAt</code> was changed from <code>May 1, 2026, 10:00:00 AM</code> to <code>May 2, 2026, 11:00:00 AM</code>'
            . '<br>Attribute <code>paid</code> was changed from <code>No</code> to <code>Yes</code>',
            $formattedChanges,
        );
    }

    public function testFormatsCollectionChangesWithCodeElements(): void
    {
        $formattedChanges = $this->resolvedChangesFormatter->formatResolvedChanges([
            'items' => [
                'dataType' => 'Collection',
                'insertedItems' => [
                    [
                        'dataType' => 'OrderItem',
                        'newReadableValue' => 'GoPay - Payment By Card',
                    ],
                ],
                'deletedItems' => [
                    [
                        'dataType' => 'OrderItem',
                        'oldReadableValue' => 'Personal collection',
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            'Collection <code>items</code> was changed:<br> Created <code>OrderItem</code>: <code>GoPay - Payment By Card</code>,<br> Removed <code>OrderItem</code>: <code>Personal collection</code>',
            $formattedChanges,
        );
    }

    public function testEscapesDynamicValuesBeforeWrappingThemInCodeElements(): void
    {
        $formattedChanges = $this->resolvedChangesFormatter->formatResolvedChanges([
            '<attribute>' => [
                'dataType' => 'string',
                'oldReadableValue' => '<old & "value">',
                'newReadableValue' => '<new & "value">',
                'oldValue' => '<old & "value">',
                'newValue' => '<new & "value">',
            ],
        ]);

        $this->assertSame(
            'Attribute <code>&lt;attribute&gt;</code> was changed from <code>&lt;old &amp; &quot;value&quot;&gt;</code> to <code>&lt;new &amp; &quot;value&quot;&gt;</code>',
            $formattedChanges,
        );
    }

    private function injectTranslatorStub(): void
    {
        $translator = $this->createStub(Translator::class);
        $translator
            ->method('trans')
            ->willReturnCallback(static fn (string $id, array $parameters = []): string => strtr($id, $parameters));

        Translator::injectSelf($translator);
    }
}
