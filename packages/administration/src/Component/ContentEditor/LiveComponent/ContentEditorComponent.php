<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\ContentEditor\LiveComponent;

use Ramsey\Uuid\Uuid;
use Shopsys\AdministrationBundle\Component\ContentEditor\Form\ContentDocumentType;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContainerContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentNormalizer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\SlotFitter;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Webmozart\Assert\Assert;

/**
 * The block list of the content editor. The state is the raw form data in $formValues, the actions only change it
 * and the form is submitted from it on every render.
 *
 * A block is addressed by its path: "2" is the third block of the document, "2/1/0" is the first block
 * in the second slot of the third block. The path of a slot is the path of its container with the slot index, e.g. "2/1".
 *
 * The actions only shape the state of the form in the browser, the content is saved with the edited entity,
 * so the access to them is not limited to the sections that use the editor.
 */
#[AsLiveComponent(
    name: self::COMPONENT_NAME,
    template: '@ShopsysAdministration/content_editor/editor.html.twig',
)]
final class ContentEditorComponent
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public const string COMPONENT_NAME = 'Admin:ContentEditor';

    public const string PICKER_OPENED_BROWSER_EVENT_PREFIX = 'content-editor:picker:opened:';
    public const string PICKER_CLOSED_BROWSER_EVENT_PREFIX = 'content-editor:picker:closed:';

    private const int SUMMARY_LENGTH = 60;

    #[LiveProp]
    public string $documentJson = '';

    #[LiveProp]
    public string $profile = ContentProfileEnum::WEB;

    /**
     * @var string[]|null
     */
    #[LiveProp]
    public ?array $allowedTypes = null;

    /**
     * @var array<int, array{label: string, placeholder: string, isRequired: bool}>
     */
    #[LiveProp]
    public array $variables = [];

    #[LiveProp]
    public string $targetInputId = '';

    #[LiveProp]
    public bool $pickerOpen = false;

    #[LiveProp]
    public string $pickerSlotPath = '';

    #[LiveProp]
    public int $pickerIndex = 0;

    public function __construct(
        private readonly ContentBlockRegistry $contentBlockRegistry,
        private readonly ContentDocumentFactory $contentDocumentFactory,
        private readonly ContentDocumentNormalizer $contentDocumentNormalizer,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    #[LiveAction]
    #[RequireRole(SystemRole::ADMIN)]
    public function openPicker(#[LiveArg] string $slotPath, #[LiveArg] int $index): void
    {
        $this->pickerSlotPath = $slotPath;
        $this->pickerIndex = $index;
        $this->pickerOpen = true;
        $this->dispatchBrowserEvent(self::PICKER_OPENED_BROWSER_EVENT_PREFIX . $this->targetInputId);
    }

    #[LiveAction]
    #[RequireRole(SystemRole::ADMIN)]
    public function closePicker(): void
    {
        $this->pickerOpen = false;
        $this->dispatchBrowserEvent(self::PICKER_CLOSED_BROWSER_EVENT_PREFIX . $this->targetInputId);
    }

    #[LiveAction]
    #[RequireRole(SystemRole::ADMIN)]
    public function addBlock(#[LiveArg] string $type): void
    {
        $offeredBlocks = $this->getOfferedBlocks();
        Assert::keyExists($offeredBlocks, $type, 'Block "%s" cannot be added here');

        $blocks = &$this->getBlockList($this->pickerSlotPath);
        array_splice($blocks, $this->pickerIndex, 0, [$this->createBlockValues($offeredBlocks[$type])]);

        $this->closePicker();
    }

    #[LiveAction]
    #[RequireRole(SystemRole::ADMIN)]
    public function moveUp(#[LiveArg] string $path): void
    {
        $this->swapWithNeighbour($path, -1);
    }

    #[LiveAction]
    #[RequireRole(SystemRole::ADMIN)]
    public function moveDown(#[LiveArg] string $path): void
    {
        $this->swapWithNeighbour($path, 1);
    }

    #[LiveAction]
    #[RequireRole(SystemRole::ADMIN)]
    public function removeBlock(#[LiveArg] string $path): void
    {
        [$slotPath, $index] = $this->splitPath($path);
        $blocks = &$this->getBlockList($slotPath);
        Assert::keyExists($blocks, $index, 'Block "%s" does not exist');

        array_splice($blocks, $index, 1);
    }

    #[LiveAction]
    #[RequireRole(SystemRole::ADMIN)]
    public function duplicateBlock(#[LiveArg] string $path): void
    {
        [$slotPath, $index] = $this->splitPath($path);
        $blocks = &$this->getBlockList($slotPath);
        Assert::keyExists($blocks, $index, 'Block "%s" does not exist');

        array_splice($blocks, $index + 1, 0, [$this->copyWithNewIds($blocks[$index])]);
    }

    #[LiveAction]
    #[RequireRole(SystemRole::ADMIN)]
    public function changeLayout(#[LiveArg] string $path, #[LiveArg] string $layout): void
    {
        [$slotPath, $index] = $this->splitPath($path);
        $blocks = &$this->getBlockList($slotPath);
        Assert::keyExists($blocks, $index, 'Block "%s" does not exist');
        $container = $this->contentBlockRegistry->get($blocks[$index]['type']);
        Assert::isInstanceOf($container, ContainerContentBlockInterface::class);

        $blocks[$index]['data']['layout'] = $layout;
        $blocks[$index]['children'] = SlotFitter::fit(
            $blocks[$index]['children'] ?? [],
            $container->getSlotCount($blocks[$index]['data']),
        );
    }

    public function getNormalizedDocumentJson(): string
    {
        $document = $this->contentDocumentFactory->createFromArray([
            'profile' => $this->profile,
            'blocks' => $this->getForm()->getData()['blocks'] ?? [],
        ]);

        return $this->contentDocumentNormalizer->normalize($document, $this->allowedTypes)->toJson();
    }

    public function getBlockDefinition(string $type): ContentBlockInterface
    {
        return $this->contentBlockRegistry->get($type);
    }

    /**
     * @return array<string, array<string, \Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface>> offered blocks by type, indexed by category
     */
    public function getPickerBlocksByCategory(): array
    {
        $blocksByCategory = [];

        foreach ($this->getOfferedBlocks() as $type => $block) {
            $blocksByCategory[$block->getCategory()][$type] = $block;
        }

        ksort($blocksByCategory);

        return $blocksByCategory;
    }

    /**
     * @param array<string, mixed> $blockValues
     */
    public function getBlockSummary(array $blockValues): string
    {
        $text = $this->contentBlockRegistry->get($blockValues['type'])->extractText($blockValues['data'] ?? []);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string)preg_replace('~\s+~u', ' ', $text));

        return mb_strimwidth($text, 0, self::SUMMARY_LENGTH, '…');
    }

    /**
     * A block is empty when the user has not put anything in it, so removing it loses nothing
     *
     * @param array<string, mixed> $blockValues
     */
    public function isBlockEmpty(array $blockValues): bool
    {
        foreach ($this->contentBlockRegistry->get($blockValues['type'])->getFields() as $field) {
            $value = $field->normalizeValue($blockValues['data'][$field->getName()] ?? null);

            if ($value !== $field->getDefault()) {
                return false;
            }
        }

        foreach ($blockValues['children'] ?? [] as $slot) {
            if ($slot !== []) {
                return false;
            }
        }

        return true;
    }

    protected function instantiateForm(): FormInterface
    {
        $document = $this->contentDocumentFactory->createFromJson($this->documentJson);
        $normalizedDocument = $this->contentDocumentNormalizer->normalize($document, $this->allowedTypes);

        return $this->formFactory->createNamed(
            'editor_' . $this->targetInputId,
            ContentDocumentType::class,
            ['blocks' => $normalizedDocument->toArray()['blocks']],
            ['variables' => $this->variables],
        );
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface>
     */
    private function getOfferedBlocks(): array
    {
        $containersAllowed = $this->pickerSlotPath === '';

        return $this->contentBlockRegistry->getForProfile($this->profile, $this->allowedTypes, $containersAllowed);
    }

    /**
     * @return array<string, mixed>
     */
    private function createBlockValues(ContentBlockInterface $block): array
    {
        $blockValues = [
            'id' => Uuid::uuid4()->toString(),
            'type' => $block->getType(),
            'data' => $block->getDefaultData(),
        ];

        if ($block instanceof ContainerContentBlockInterface) {
            $blockValues['children'] = array_fill(0, $block->getSlotCount($blockValues['data']), []);
        }

        return $blockValues;
    }

    private function swapWithNeighbour(string $path, int $offset): void
    {
        [$slotPath, $index] = $this->splitPath($path);
        $blocks = &$this->getBlockList($slotPath);
        Assert::keyExists($blocks, $index, 'Block "%s" does not exist');

        if (isset($blocks[$index + $offset])) {
            [$blocks[$index + $offset], $blocks[$index]] = [$blocks[$index], $blocks[$index + $offset]];
        }
    }

    /**
     * @param array<string, mixed> $blockValues
     * @return array<string, mixed>
     */
    private function copyWithNewIds(array $blockValues): array
    {
        $blockValues['id'] = Uuid::uuid4()->toString();

        foreach ($blockValues['children'] ?? [] as $slotIndex => $slot) {
            foreach ($slot as $childIndex => $childValues) {
                $blockValues['children'][$slotIndex][$childIndex] = $this->copyWithNewIds($childValues);
            }
        }

        return $blockValues;
    }

    /**
     * @return array{0: string, 1: int} path of the slot with the block and index of the block in it
     */
    private function splitPath(string $path): array
    {
        $separatorPosition = strrpos($path, '/');

        if ($separatorPosition === false) {
            return ['', (int)$path];
        }

        return [substr($path, 0, $separatorPosition), (int)substr($path, $separatorPosition + 1)];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function &getBlockList(string $slotPath): array
    {
        $blocks = &$this->formValues['blocks'];

        if ($slotPath === '') {
            return $blocks;
        }

        [$blockIndex, $slotIndex] = array_map('intval', explode('/', $slotPath));
        Assert::keyExists($blocks, $blockIndex, 'Block "%s" does not exist');
        Assert::keyExists($blocks[$blockIndex]['children'] ?? [], $slotIndex, 'Slot "%s" does not exist');

        return $blocks[$blockIndex]['children'][$slotIndex];
    }
}
