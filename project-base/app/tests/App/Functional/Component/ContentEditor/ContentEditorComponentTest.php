<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\ContentEditor;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Shopsys\AdministrationBundle\Component\ContentEditor\LiveComponent\ContentEditorComponent;
use Shopsys\AdministrationBundle\Form\Admin\Type\ContentEditorType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
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
    private const string VIDEO_ID = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';

    /**
     * @inject
     */
    private RequestStack $requestStack;

    private KernelBrowser $client;

    /**
     * @inject
     */
    private AdministratorActivityFacade $administratorActivityFacade;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

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
            ['id' => self::VIDEO_ID, 'type' => 'video', 'data' => ['url' => 'https://example.com/not-a-video']],
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
            ['id' => self::VIDEO_ID, 'type' => 'video', 'data' => ['url' => 'https://example.com/not-a-video']],
        ], true);
        $this->client->catchExceptions(true);

        $component->call('moveDown', ['path' => '0']);

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
                [['id' => self::VIDEO_ID, 'type' => 'video', 'data' => ['url' => 'https://example.com/not-a-video']]],
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
        $component->call('addBlock', ['type' => 'video', 'slotPath' => '', 'index' => 0]);
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
        $this->assertSame([ContentEditorComponent::DONE_BROWSER_EVENT], array_column($events, 'event'));
        $document = json_decode($events[0]['payload']['document'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(1, $document['version']);
        $this->assertSame('web', $document['profile']);
        $this->assertSame(
            [['id' => self::TEXT_ID_1, 'type' => 'text', 'data' => ['html' => '<p>One</p>']]],
            $document['blocks'],
        );
    }

    public function testExportedDocumentContainsStorefrontFacingData(): void
    {
        $component = $this->createEditor([
            ['id' => self::TEXT_ID_1, 'type' => 'products', 'data' => ['productIds' => [1]]],
            $this->createColumnsBlock('columns-1-2', [[$this->createTextBlock(self::TEXT_ID_2, '<p>One</p>')], []]),
            ['id' => self::TEXT_ID_3, 'type' => 'video', 'data' => ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']],
        ]);

        $blocks = $this->getDocument($component)['blocks'];

        $this->assertSame([$this->productFacade->getById(1)->getCatnum()], $blocks[0]['data']['catnums']);
        $this->assertSame('1/3-2/3', $blocks[1]['data']['layout']);
        $this->assertSame('<p>One</p>', $blocks[1]['children'][0][0]['data']['html']);
        $this->assertSame('youtube', $blocks[2]['data']['provider']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $blocks[2]['data']['embedUrl']);
    }

    public function testPreviewIsExportedForTheGivenDomainOrTheOneWithTheGivenLocale(): void
    {
        $firstDomainConfig = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);
        $secondDomainConfig = $this->domain->getDomainConfigById(Domain::SECOND_DOMAIN_ID);
        $this->assertNotSame($firstDomainConfig->getLocale(), $secondDomainConfig->getLocale());

        $this->assertSame(Domain::SECOND_DOMAIN_ID, $this->resolveExportDomainId(['domainId' => Domain::SECOND_DOMAIN_ID]));
        $this->assertSame(Domain::SECOND_DOMAIN_ID, $this->resolveExportDomainId(['locale' => $secondDomainConfig->getLocale()]));
        $this->assertSame(Domain::FIRST_DOMAIN_ID, $this->resolveExportDomainId(['locale' => 'xx']));
        $this->assertSame(Domain::FIRST_DOMAIN_ID, $this->resolveExportDomainId([]));
    }

    public function testWebProfileRendersPreviewFrameWithDeviceToggle(): void
    {
        $crawler = $this->createEditor([])->render()->crawler();

        $frame = $crawler->filter('[data-tid="content-editor-preview"] iframe[data-content-editor-target="previewFrame"]');
        $this->assertCount(1, $frame);
        $this->assertSame('/content-editor-preview', $frame->attr('src'));
        $this->assertCount(1, $crawler->filter('[data-tid="content-editor-preview-desktop"][data-content-editor-width-param="100%"]'));
        $this->assertCount(1, $crawler->filter('[data-tid="content-editor-preview-mobile"][data-content-editor-width-param="390px"]'));
    }

    public function testBlockFieldsAreBoundToLiveModel(): void
    {
        $component = $this->createEditor([
            $this->createTextBlock(self::TEXT_ID_1, '<p>One</p>'),
            ['id' => self::TEXT_ID_2, 'type' => 'button-link', 'data' => ['text' => 'Go', 'url' => '/', 'variant' => 'primary']],
        ]);

        $crawler = $component->render()->crawler();

        $this->assertCount(1, $crawler->filter('textarea[data-model="debounce(500)|editor_content[blocks][0][data][html]"]'));
        $this->assertCount(1, $crawler->filter('input[data-model="debounce(300)|editor_content[blocks][1][data][text]"]'));
        $this->assertCount(1, $crawler->filter('select[data-model="debounce(300)|editor_content[blocks][1][data][variant]"]'));
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
        // a closed picker does not query the library
        $this->assertCount(0, $crawler->filter('[data-tid="media-picker-empty"], [data-tid^="media-picker-item-"]'));
    }

    public function testBlockIsAddedAtTopLevelAtTheChosenPosition(): void
    {
        $component = $this->createEditor([
            $this->createTextBlock(self::TEXT_ID_1, '<p>One</p>'),
            $this->createTextBlock(self::TEXT_ID_2, '<p>Two</p>'),
        ]);

        $component->call('addBlock', ['type' => 'image', 'slotPath' => '', 'index' => 1]);

        $blocks = $this->getDocument($component)['blocks'];
        $this->assertSame(['text', 'image', 'text'], array_column($blocks, 'type'));
        $this->assertSame(self::TEXT_ID_2, $blocks[2]['id']);
        $this->assertArrayHasKey('mediaId', $blocks[1]['data']);
        $this->assertArrayNotHasKey('children', $blocks[1]);
    }

    #[DataProvider('containerTypeProvider')]
    public function testAddedContainerHasEmptySlotsForItsLayout(string $type, int $slotCount): void
    {
        $component = $this->createEditor([]);

        $component->call('addBlock', ['type' => $type, 'slotPath' => '', 'index' => 0]);

        $block = $this->getDocument($component)['blocks'][0];
        $this->assertSame($type, $block['type']);
        $this->assertSame(array_fill(0, $slotCount, []), $block['children']);
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function containerTypeProvider(): iterable
    {
        yield 'two columns' => ['columns-1-1', 2];

        yield 'three columns' => ['columns-1-1-1', 3];
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

    public function testBlockIsAddedInsideSlot(): void
    {
        $component = $this->createEditor([$this->createColumnsBlock('columns-1-1', [[], []])]);

        $component->call('addBlock', ['type' => 'text', 'slotPath' => '0/1', 'index' => 0]);

        $children = $this->getDocument($component)['blocks'][0]['children'];
        $this->assertSame([], $children[0]);
        $this->assertSame(['text'], array_column($children[1], 'type'));
    }

    public function testPickerMarksContainersSoThatTheyCanBeHiddenInSlots(): void
    {
        $crawler = $this->createEditor([])->render()->crawler();

        $containerTiles = $crawler->filter('[data-tid^="content-editor-picker-tile-columns-"]');
        $this->assertGreaterThan(0, $containerTiles->count());
        $this->assertCount($containerTiles->count(), $crawler->filter('[data-tid^="content-editor-picker-tile-columns-"][data-container]'));
        $this->assertCount(1, $crawler->filter('[data-tid="content-editor-picker-tile-text"]:not([data-container])'));
    }

    public function testContainerCannotBeAddedInsideSlot(): void
    {
        $component = $this->createEditor([$this->createColumnsBlock('columns-1-1', [[], []])]);

        $this->expectException(InvalidArgumentException::class);

        $component->call('addBlock', ['type' => 'columns-1-1', 'slotPath' => '0/0', 'index' => 0]);
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

    public function testHeadingBlockIsAddedWithDefaultLevelAndRendersItsFields(): void
    {
        $component = $this->createEditor([]);

        $component->call('addBlock', ['type' => 'heading', 'slotPath' => '', 'index' => 0]);

        $blocks = $this->getDocument($component)['blocks'];
        $this->assertSame('heading', $blocks[0]['type']);
        $this->assertSame(['text' => '', 'level' => 'h2'], $blocks[0]['data']);

        $crawler = $component->render()->crawler();
        $this->assertCount(1, $crawler->filter('input[data-model="debounce(300)|editor_content[blocks][0][data][text]"]'));
        $this->assertCount(1, $crawler->filter('select[data-model="debounce(300)|editor_content[blocks][0][data][level]"]'));
        $this->assertCount(1, $crawler->filter('[data-tid="content-editor-picker-tile-heading"]'));
    }

    public function testDiscardResetsTheEditorToTheGivenDocument(): void
    {
        $originalDocument = json_encode(
            ['version' => 1, 'profile' => 'web', 'blocks' => $this->createThreeTextBlocks()],
            JSON_THROW_ON_ERROR,
        );
        $component = $this->createEditor($this->createThreeTextBlocks());
        $originalExport = $this->getDocument($component);
        $component->call('removeBlock', ['path' => '0']);
        $component->call('moveDown', ['path' => '0']);
        $this->assertNotSame($originalExport, $this->getDocument($component));

        $component->call('discard', ['documentJson' => $originalDocument]);

        $this->assertSame([self::TEXT_ID_1, self::TEXT_ID_2, self::TEXT_ID_3], $this->getBlockIds($component));
        $this->assertSame($originalExport, $this->getDocument($component));
        $this->assertCount(3, $component->render()->crawler()->filter('[data-tid="content-editor-block"]'));
    }

    public function testEditorHasCloseButtonNextToDone(): void
    {
        $crawler = $this->createEditor([])->render()->crawler();

        $this->assertCount(1, $crawler->filter('.modal-header button[data-tid="content-editor-close"][data-action="content-editor-overlay#close"]'));
        $this->assertCount(1, $crawler->filter('.modal-header [data-tid="content-editor-done"]'));
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
     * @param array<string, mixed> $props
     */
    private function createEditor(array $blocks, bool $validated = false, array $props = []): TestLiveComponent
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
            ...$props,
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
        $json = $component->render()->crawler()->filter('[data-content-editor-exported-document-value]')->attr('data-content-editor-exported-document-value');

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $props
     */
    private function resolveExportDomainId(array $props): int
    {
        $component = $this->createEditor([], false, $props)->component();

        // private on purpose, the preview export is its only user
        return (new ReflectionMethod($component, 'resolveExportDomainConfig'))->invoke($component)->getId();
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
