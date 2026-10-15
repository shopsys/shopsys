<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\ContentEditor;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use InvalidArgumentException;
use Shopsys\AdministrationBundle\Component\ContentEditor\LiveComponent\ContentEditorComponent;
use Shopsys\AdministrationBundle\Form\Admin\Type\ContentEditorType;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;
use Symfony\UX\LiveComponent\Test\TestLiveComponent;
use Tests\App\Test\TransactionFunctionalTestCase;

class ContentEditorComponentTest extends TransactionFunctionalTestCase
{
    use InteractsWithLiveComponents;

    private const string ADMIN_IP_ADDRESS = '127.0.0.1';

    private const string TEXT_ID_1 = '11111111-1111-4111-8111-111111111111';
    private const string TEXT_ID_2 = '22222222-2222-4222-8222-222222222222';
    private const string TEXT_ID_3 = '33333333-3333-4333-8333-333333333333';
    private const string COLUMNS_ID = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
    private const string TABLE_ID = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';

    /**
     * @inject
     */
    private RequestStack $requestStack;

    private KernelBrowser $client;

    /**
     * @inject
     */
    private AdministratorActivityFacade $administratorActivityFacade;

    public function testEmptyDocumentShowsFirstBlockTileAndPickerWithAllGroups(): void
    {
        $component = $this->createEditor([]);

        $crawler = $component->render()->crawler();

        $this->assertCount(1, $crawler->filter('[data-tid="content-editor-add-first-block"]'));
        $this->assertCount(0, $crawler->filter('[data-tid="content-editor-block"]'));
        $this->assertCount(1, $crawler->filter('[data-tid="content-editor-picker-category-content"]'));
        $this->assertCount(1, $crawler->filter('[data-tid="content-editor-picker-category-layout"]'));
        $this->assertCount(1, $crawler->filter('[data-tid="content-editor-picker-tile-text"]'));
        $this->assertCount(4, $crawler->filter('[data-tid^="content-editor-picker-tile-columns-"]'));
    }

    public function testPickerHasFilterInputAndGroupsAndTilesToFilter(): void
    {
        $crawler = $this->createEditor([])->render()->crawler();

        $this->assertCount(1, $crawler->filter('[data-controller~="block-picker-filter"] input[data-tid="content-editor-picker-filter"]'));
        $this->assertCount(2, $crawler->filter('[data-block-picker-filter-target="group"]'));
        $this->assertCount(
            $crawler->filter('[data-tid^="content-editor-picker-tile-"]')->count(),
            $crawler->filter('[data-block-picker-filter-target="tile"]'),
        );
    }

    public function testErrorsOfInvalidBlocksAreShownOnlyWhenTheContentWasRefused(): void
    {
        $blocks = [
            $this->createTextBlock(self::TEXT_ID_1, '<p>One</p>'),
            ['id' => self::TABLE_ID, 'type' => 'video', 'data' => ['url' => 'https://example.com/not-a-video']],
        ];

        $editing = $this->renderEditorOnPage($blocks, false);
        $this->assertStringNotContainsString('Enter a YouTube or Vimeo video URL.', $editing->text());
        $this->assertCount(0, $editing->filter('[data-content-editor-has-errors]'));

        $refused = $this->renderEditorOnPage($blocks, true);
        $this->assertStringContainsString('Enter a YouTube or Vimeo video URL.', $refused->text());
        $this->assertCount(1, $refused->filter('[data-content-editor-has-errors]'));
    }

    public function testRefusedContentKeepsShowingErrorsInTheFollowingRenders(): void
    {
        $component = $this->createEditor([
            ['id' => self::TABLE_ID, 'type' => 'video', 'data' => ['url' => 'https://example.com/not-a-video']],
        ], true);
        $this->client->catchExceptions(true);

        $component->call('openPicker', ['slotPath' => '', 'index' => 1]);

        $crawler = $component->render()->crawler();
        $this->assertStringContainsString('Enter a YouTube or Vimeo video URL.', $crawler->text());
        $this->assertCount(1, $crawler->filter('[data-content-editor-has-errors]'));
    }

    public function testFinishWithInvalidBlocksShowsErrorsAndDoesNotAnnounceTheDocument(): void
    {
        $component = $this->createEditor([
            $this->createTextBlock(self::TEXT_ID_1, '<p>One</p>'),
            $this->createColumnsBlock('columns-1-1', [
                [],
                [['id' => self::TABLE_ID, 'type' => 'video', 'data' => ['url' => 'https://example.com/not-a-video']]],
            ]),
        ]);
        // the Live Component test helper lets exceptions through, the browser gets the rendered editor instead
        $this->client->catchExceptions(true);

        $response = $component->call('finish')->response();

        $crawler = new Crawler($response->getContent());
        $this->assertStringContainsString('Enter a YouTube or Vimeo video URL.', $crawler->text());
        // text block, columns block, its first column, its second column, the video block in it
        $this->assertSame(
            [false, true, false, true, true],
            $crawler->filter('[data-tid="content-editor-block"], [data-tid="content-editor-slot"]')->each(
                static fn (Crawler $element): bool => $element->attr('data-content-editor-has-errors') !== null,
            ),
        );
        $this->assertSame([], $this->getBrowserEvents($response->getContent()));
    }

    public function testFinishWithEmptyRequiredFieldShowsErrorAndDoesNotAnnounceTheDocument(): void
    {
        $component = $this->createEditor([]);
        $component->call('addBlock', ['type' => 'video']);
        // the Live Component test helper lets exceptions through, the browser gets the rendered editor instead
        $this->client->catchExceptions(true);

        $response = $component->call('finish')->response();

        $crawler = new Crawler($response->getContent());
        $this->assertStringContainsString('This value should not be blank.', $crawler->text());
        $this->assertSame([], $this->getBrowserEvents($response->getContent()));
    }

    public function testFinishWithValidBlocksAnnouncesTheNormalizedDocument(): void
    {
        $component = $this->createEditor([$this->createTextBlock(self::TEXT_ID_1, '<p>One</p>')]);

        $response = $component->call('finish')->response();

        $events = $this->getBrowserEvents($response->getContent());
        $this->assertSame(['content-editor:done:content'], array_column($events, 'event'));
        $document = json_decode($events[0]['payload']['document'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(
            [['id' => self::TEXT_ID_1, 'type' => 'text', 'data' => ['html' => '<p>One</p>']]],
            $document['blocks'],
        );
    }

    public function testNormalizedDocumentJsonHasDocumentShape(): void
    {
        $component = $this->createEditor([$this->createTextBlock(self::TEXT_ID_1, '<p>One</p>')]);

        $document = $this->getDocument($component);

        $this->assertSame(1, $document['version']);
        $this->assertSame('web', $document['profile']);
        $this->assertSame(
            [['id' => self::TEXT_ID_1, 'type' => 'text', 'data' => ['html' => '<p>One</p>']]],
            $document['blocks'],
        );
    }

    public function testBlockFieldsAreBoundToLiveModel(): void
    {
        $component = $this->createEditor([
            $this->createTextBlock(self::TEXT_ID_1, '<p>One</p>'),
            ['id' => self::TABLE_ID, 'type' => 'table', 'data' => ['rows' => 1, 'columns' => 1, 'variant' => 'default', 'cells' => [['']]]],
        ]);

        $crawler = $component->render()->crawler();

        $this->assertCount(1, $crawler->filter('textarea[data-model="norender|editor_content[blocks][0][data][html]"]'));
        $this->assertCount(1, $crawler->filter('input[data-model="debounce(300)|editor_content[blocks][1][data][rows]"]'));
        $this->assertCount(1, $crawler->filter('select[data-model="debounce(300)|editor_content[blocks][1][data][variant]"]'));
        $this->assertCount(1, $crawler->filter('textarea[data-model="norender|editor_content[blocks][1][data][cells][0][0]"]'));
        $this->assertCount(0, $crawler->filter('form'));
    }

    public function testProductsFieldHasWrapperWithModelNameForSyncController(): void
    {
        $component = $this->createEditor([
            ['id' => self::TEXT_ID_1, 'type' => 'products', 'data' => ['productIds' => []]],
        ]);

        $crawler = $component->render()->crawler();

        $wrapper = $crawler->filter('[data-controller="product-ids-sync"][data-live-ignore]');
        $this->assertCount(1, $wrapper);
        $this->assertSame('editor_content[blocks][0][data][productIds]', $wrapper->attr('data-product-ids-sync-model-value'));
        $this->assertCount(0, $crawler->filter('[data-model*="productIds"]'));
    }

    public function testProductsFieldRendersSelectedProducts(): void
    {
        $component = $this->createEditor([
            ['id' => self::TEXT_ID_1, 'type' => 'products', 'data' => ['productIds' => [1]]],
        ]);

        $crawler = $component->render()->crawler();

        $this->assertCount(1, $crawler->filter('[data-controller="product-ids-sync"]'));
        $this->assertGreaterThan(0, $crawler->filter('[data-controller="product-ids-sync"] input[type="hidden"]')->count());
    }

    public function testProductsFieldDropsProductsThatNoLongerExist(): void
    {
        $component = $this->createEditor([
            ['id' => self::TEXT_ID_1, 'type' => 'products', 'data' => ['productIds' => [1, 999999999]]],
        ]);

        $inputs = $component->render()->crawler()->filter('[data-controller="product-ids-sync"] input[type="hidden"]');

        $values = $inputs->each(static fn (Crawler $input): string => (string)$input->attr('value'));
        $this->assertSame(['1'], $values);
    }

    public function testEditorWidgetRendersOneMediaPickerWithInstanceIdAndImageField(): void
    {
        $this->createEditor([]);
        $form = self::getContainer()->get('form.factory')->createNamed('article_form', FormType::class)
            ->add('content', ContentEditorType::class, ['data' => null]);
        $this->requestStack->push($this->createRequestWithSession());

        $html = self::getContainer()->get('twig')->createTemplate('{{ form_widget(form.content) }}')->render(['form' => $form->createView()]);

        $crawler = new Crawler($html);
        $this->assertCount(1, $crawler->filter('[data-content-editor-widget]'));
        $this->assertCount(1, $crawler->filter('[data-content-editor-widget] [data-live-name-value="Admin:ContentEditor:MediaPicker"]'));
        $this->assertCount(1, $crawler->filter('[data-content-editor-widget] button[data-tid="content-editor-open"]'));
        $overlay = $crawler->filter('.modal[data-tid="content-editor-overlay"][data-bs-backdrop="static"][data-bs-keyboard="false"]');
        $this->assertCount(1, $overlay);
        $this->assertCount(1, $overlay->filter('.modal-dialog.modal-fullscreen [data-live-name-value="Admin:ContentEditor"]'));
        $this->assertCount(1, $overlay->filter('[data-tid="content-editor-done"]'));
        $this->assertCount(1, $overlay->filter('[data-tid="content-editor-picker-filter"]'));
        $this->assertCount(1, $overlay->filter('[data-live-name-value="Admin:ContentEditor:MediaPicker"]'));
        $this->assertSame('false', $crawler->filter('[data-controller="content-editor-overlay"]')->attr('data-content-editor-overlay-open-on-connect-value'));
        $this->assertStringContainsString('content-editor:media-picker:opened:article_form_content', $html);
        // a closed picker does not query the library
        $this->assertCount(0, $crawler->filter('[data-tid="media-picker-empty"], [data-tid^="media-picker-item-"]'));
    }

    public function testBlockIsAddedAtTopLevelAtTheChosenPosition(): void
    {
        $component = $this->createEditor([
            $this->createTextBlock(self::TEXT_ID_1, '<p>One</p>'),
            $this->createTextBlock(self::TEXT_ID_2, '<p>Two</p>'),
        ]);

        $component->call('openPicker', ['slotPath' => '', 'index' => 1]);
        $component->call('addBlock', ['type' => 'image']);

        $blocks = $this->getDocument($component)['blocks'];
        $this->assertSame(['text', 'image', 'text'], array_column($blocks, 'type'));
        $this->assertSame(self::TEXT_ID_2, $blocks[2]['id']);
        $this->assertArrayHasKey('mediaId', $blocks[1]['data']);
        $this->assertArrayNotHasKey('children', $blocks[1]);
    }

    public function testAddedContainerHasEmptySlotsForItsLayout(): void
    {
        $component = $this->createEditor([]);

        $component->call('openPicker', ['slotPath' => '', 'index' => 0]);
        $component->call('addBlock', ['type' => 'columns-1-1']);

        $block = $this->getDocument($component)['blocks'][0];
        $this->assertSame('columns-1-1', $block['type']);
        $this->assertSame([[], []], $block['children']);
    }

    public function testAddedThreeColumnsHaveThreeEmptySlots(): void
    {
        $component = $this->createEditor([]);

        $component->call('openPicker', ['slotPath' => '', 'index' => 0]);
        $component->call('addBlock', ['type' => 'columns-1-1-1']);

        $this->assertSame([[], [], []], $this->getDocument($component)['blocks'][0]['children']);
    }

    public function testColumnsRenderTheirSlotsAsCollapsibleSectionsWithOwnBlockLists(): void
    {
        $component = $this->createEditor([$this->createColumnsBlock('columns-1-2', [
            [$this->createTextBlock(self::TEXT_ID_1, '<p>One</p>')],
            [],
        ])]);

        $crawler = $component->render()->crawler();

        $slots = $crawler->filter('[data-tid="content-editor-slot"]');
        $this->assertCount(2, $slots);
        $this->assertSame(
            [self::COLUMNS_ID . ':slot:0', self::COLUMNS_ID . ':slot:1'],
            $slots->each(static fn (Crawler $slot): ?string => $slot->attr('data-content-editor-key')),
        );
        $this->assertStringContainsString('Column 1', $slots->eq(0)->text());
        $this->assertStringContainsString('Column 2', $slots->eq(1)->text());
        $this->assertCount(1, $slots->eq(0)->filter('[data-tid="content-editor-block"]'));
        $this->assertCount(1, $slots->eq(1)->filter('[data-tid="content-editor-add-block"]'));
        $this->assertCount(0, $crawler->filter('[data-tid^="content-editor-layout"]'));
    }

    public function testBlockIsAddedInsideSlotAndContainersAreNotOfferedThere(): void
    {
        $component = $this->createEditor([$this->createColumnsBlock('columns-1-1', [[], []])]);

        $component->call('openPicker', ['slotPath' => '0/1', 'index' => 0]);
        $crawler = $component->render()->crawler();
        $this->assertCount(1, $crawler->filter('[data-tid="content-editor-picker-tile-text"]'));
        $this->assertCount(0, $crawler->filter('[data-tid^="content-editor-picker-tile-columns-"]'));

        $component->call('addBlock', ['type' => 'text']);

        $children = $this->getDocument($component)['blocks'][0]['children'];
        $this->assertSame([], $children[0]);
        $this->assertSame(['text'], array_column($children[1], 'type'));
    }

    public function testContainerCannotBeAddedInsideSlot(): void
    {
        $component = $this->createEditor([$this->createColumnsBlock('columns-1-1', [[], []])]);
        $component->call('openPicker', ['slotPath' => '0/0', 'index' => 0]);

        $this->expectException(InvalidArgumentException::class);

        $component->call('addBlock', ['type' => 'columns-1-1']);
    }

    public function testBlockMovesUpAndDownAndStaysAtBoundaries(): void
    {
        $component = $this->createEditor($this->createThreeTextBlocks());

        $component->call('moveUp', ['path' => '0']);
        $this->assertSame([self::TEXT_ID_1, self::TEXT_ID_2, self::TEXT_ID_3], $this->getBlockIds($component));

        $component->call('moveDown', ['path' => '0']);
        $this->assertSame([self::TEXT_ID_2, self::TEXT_ID_1, self::TEXT_ID_3], $this->getBlockIds($component));

        $component->call('moveUp', ['path' => '2']);
        $this->assertSame([self::TEXT_ID_2, self::TEXT_ID_3, self::TEXT_ID_1], $this->getBlockIds($component));

        $component->call('moveDown', ['path' => '2']);
        $this->assertSame([self::TEXT_ID_2, self::TEXT_ID_3, self::TEXT_ID_1], $this->getBlockIds($component));
    }

    public function testBlockMovesInsideSlot(): void
    {
        $component = $this->createEditor([$this->createColumnsBlock('columns-1-1', [
            [$this->createTextBlock(self::TEXT_ID_1, '<p>One</p>'), $this->createTextBlock(self::TEXT_ID_2, '<p>Two</p>')],
            [],
        ])]);

        $component->call('moveDown', ['path' => '0/0/0']);

        $children = $this->getDocument($component)['blocks'][0]['children'];
        $this->assertSame([self::TEXT_ID_2, self::TEXT_ID_1], array_column($children[0], 'id'));
    }

    public function testBlockIsRemoved(): void
    {
        $component = $this->createEditor($this->createThreeTextBlocks());

        $component->call('removeBlock', ['path' => '1']);

        $this->assertSame([self::TEXT_ID_1, self::TEXT_ID_3], $this->getBlockIds($component));
    }

    public function testLastBlockRemovalShowsFirstBlockTileAgain(): void
    {
        $component = $this->createEditor([$this->createTextBlock(self::TEXT_ID_1, '<p>One</p>')]);

        $component->call('removeBlock', ['path' => '0']);

        $this->assertSame([], $this->getBlockIds($component));
        $this->assertCount(1, $component->render()->crawler()->filter('[data-tid="content-editor-add-first-block"]'));
    }

    public function testDuplicatedBlockHasSameDataNewIdAndIsRightAfterTheOriginal(): void
    {
        $component = $this->createEditor($this->createThreeTextBlocks());

        $component->call('duplicateBlock', ['path' => '0']);

        $blocks = $this->getDocument($component)['blocks'];
        $this->assertCount(4, $blocks);
        $this->assertSame(self::TEXT_ID_1, $blocks[0]['id']);
        $this->assertNotSame(self::TEXT_ID_1, $blocks[1]['id']);
        $this->assertSame($blocks[0]['data'], $blocks[1]['data']);
        $this->assertSame(self::TEXT_ID_2, $blocks[2]['id']);
    }

    public function testDuplicatedContainerGetsNewIdsForItsChildren(): void
    {
        $component = $this->createEditor([$this->createColumnsBlock('columns-1-1', [
            [$this->createTextBlock(self::TEXT_ID_1, '<p>One</p>')],
            [],
        ])]);

        $component->call('duplicateBlock', ['path' => '0']);

        $blocks = $this->getDocument($component)['blocks'];
        $copy = $blocks[1];
        $this->assertNotSame(self::COLUMNS_ID, $copy['id']);
        $this->assertSame('<p>One</p>', $copy['children'][0][0]['data']['html']);
        $this->assertNotSame(self::TEXT_ID_1, $copy['children'][0][0]['id']);
    }

    public function testTableCellsFollowChangedNumberOfRows(): void
    {
        $component = $this->createEditor([
            ['id' => self::TABLE_ID, 'type' => 'table', 'data' => ['rows' => 2, 'columns' => 2, 'variant' => 'default', 'cells' => [['a', 'b'], ['c', 'd']]]],
        ]);

        $component->set('editor_content.blocks.0.data.rows', '3');

        $data = $this->getDocument($component)['blocks'][0]['data'];
        $this->assertSame(3, $data['rows']);
        $this->assertSame([['a', 'b'], ['c', 'd'], ['', '']], $data['cells']);
        $this->assertCount(3, $component->render()->crawler()->filter('table[data-tid="table-cells"] tr'));
    }

    public function testBlockSummaryAndEmptinessAreExposedPerBlock(): void
    {
        $component = $this->createEditor([
            $this->createTextBlock(self::TEXT_ID_1, '<p>Hello <strong>world</strong></p>'),
            $this->createTextBlock(self::TEXT_ID_2, ''),
        ]);

        $crawler = $component->render()->crawler();

        $summaries = $crawler->filter('[data-tid="content-editor-block-summary"]')->each(static fn ($node): string => trim($node->text()));
        $this->assertSame(['Hello world', ''], $summaries);
        $emptiness = $crawler->filter('[data-tid="content-editor-block"]')->each(static fn ($node): ?string => $node->attr('data-content-editor-empty'));
        $this->assertSame(['0', '1'], $emptiness);
    }

    private function createRequestWithSession(): Request
    {
        $request = Request::create('/');
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     */
    private function createEditor(array $blocks, bool $validated = false): TestLiveComponent
    {
        // the project test client sends JSON by default, the Live Component expects a form post
        $client = new KernelBrowser(self::$kernel, [
            'HTTP_HOST' => '127.0.0.1:8000',
            // the headers the Live Component JS sends, the test mode of the Live Component bundle is not enabled in this project
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/vnd.live-component+html',
        ]);

        // a rebooted kernel would drop the transaction of the test
        $client->disableReboot();
        $this->client = $client;

        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->administratorActivityFacade->create($administrator, self::ADMIN_IP_ADDRESS);
        $client->loginUser($administrator, 'administration');

        return $this->createLiveComponent(ContentEditorComponent::COMPONENT_NAME, [
            'documentJson' => json_encode(['version' => 1, 'profile' => 'web', 'blocks' => $blocks], JSON_THROW_ON_ERROR),
            'profile' => 'web',
            'targetInputId' => 'content',
            'isValidated' => $validated,
        ], $client);
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     */
    private function renderEditorOnPage(array $blocks, bool $validated): Crawler
    {
        $html = self::getContainer()->get('twig')->createTemplate("{{ component('Admin:ContentEditor', props) }}")->render([
            'props' => [
                'documentJson' => json_encode(['version' => 1, 'profile' => 'web', 'blocks' => $blocks], JSON_THROW_ON_ERROR),
                'profile' => 'web',
                'targetInputId' => 'content',
                'isValidated' => $validated,
            ],
        ]);

        return new Crawler($html);
    }

    /**
     * @return array<int, array{event: string, payload: array<string, mixed>}>
     */
    private function getBrowserEvents(string $html): array
    {
        $events = (new Crawler($html))->filter('[data-live-name-value="Admin:ContentEditor"]')->attr('data-live-events-to-dispatch-value');

        return $events === null ? [] : json_decode($events, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{version: int, profile: string, blocks: array<int, array<string, mixed>>}
     */
    private function getDocument(TestLiveComponent $component): array
    {
        $json = $component->render()->crawler()->filter('[data-content-editor-document-value]')->attr('data-content-editor-document-value');

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return string[]
     */
    private function getBlockIds(TestLiveComponent $component): array
    {
        return array_column($this->getDocument($component)['blocks'], 'id');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function createThreeTextBlocks(): array
    {
        return [
            $this->createTextBlock(self::TEXT_ID_1, '<p>One</p>'),
            $this->createTextBlock(self::TEXT_ID_2, '<p>Two</p>'),
            $this->createTextBlock(self::TEXT_ID_3, '<p>Three</p>'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function createTextBlock(string $id, string $html): array
    {
        return ['id' => $id, 'type' => 'text', 'data' => ['html' => $html]];
    }

    /**
     * @param array<int, array<int, array<string, mixed>>> $children
     * @return array<string, mixed>
     */
    private function createColumnsBlock(string $type, array $children): array
    {
        return ['id' => self::COLUMNS_ID, 'type' => $type, 'data' => [], 'children' => $children];
    }
}
