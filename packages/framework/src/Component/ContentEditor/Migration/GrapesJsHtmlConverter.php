<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Migration;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailButtonBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ButtonLinkBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\IframeBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ImageBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\VideoUrlParser;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\ContentEditor\Sanitizer\ContentHtmlSanitizer;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

/**
 * Converts the HTML saved by GrapesJS to a content document. The mapping of the markup to blocks is described in
 * docs/plans/cms-block-editor.md (section J). Only what is dropped or cannot be converted is reported in the notes,
 * stripped inline styles are not.
 */
class GrapesJsHtmlConverter
{
    private const array BLOCK_TYPES = [
        ContentProfileEnum::WEB => [
            'text' => 'text',
            'image' => 'image',
            'button' => 'button-link',
        ],
        ContentProfileEnum::MAIL => [
            'text' => 'mail-text',
            'image' => 'mail-image',
            'button' => 'mail-button',
        ],
    ];

    private const array BLOCK_SELECTORS = [
        ContentProfileEnum::WEB => '.gjs-text-ckeditor, .gjs-products, .row, .gjs-text-with-image, .gjs-table-custom, iframe',
        ContentProfileEnum::MAIL => '.gjs-text-ckeditor, table',
    ];

    // converted or removed by the sanitizer without a loss
    private const array PLAIN_ELEMENTS = ['h1', 'div'];

    private const string BLOCK_ELEMENTS_PATTERN = '~^<(p|h[1-6]|ul|ol|blockquote|hr|div|table)\b~i';

    private string $profile;

    /**
     * @var string[]
     */
    private array $notes = [];

    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly LegacyImageImporter $legacyImageImporter,
        private readonly ContentHtmlSanitizer $contentHtmlSanitizer,
        private readonly VideoUrlParser $videoUrlParser,
        private readonly ContentDocumentFactory $contentDocumentFactory,
    ) {
    }

    public function convert(string $html, string $profile): ConversionResult
    {
        $this->profile = $profile;
        $this->notes = [];

        $document = HTMLDocument::createFromString('<body>' . $html, LIBXML_NOERROR, 'UTF-8');
        $blocks = $this->convertChildren($document->body);

        return new ConversionResult($this->contentDocumentFactory->createEmpty($profile)->withBlocks($blocks), $this->notes);
    }

    /**
     * Elements that map to a block are converted, the rest (paragraphs, headings, lists, stray text) is collected
     * to text blocks. A mail written without GrapesJS text containers is then one text, not a block per paragraph.
     * The text may hold images and buttons (mail templates do), they become blocks of their own and the text
     * around them is split into more text blocks.
     *
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertChildren(Node $parent): array
    {
        $blocks = [];
        $html = '';

        foreach ($parent->childNodes as $child) {
            if ($child instanceof Text) {
                $html .= htmlspecialchars($child->data);

                continue;
            }

            if (!($child instanceof Element)) {
                continue;
            }

            if ($this->isBlockElement($child) || $this->containsBlockElement($child)) {
                array_push($blocks, ...$this->createTextBlocks($html));
                $html = '';
                array_push($blocks, ...($this->isBlockElement($child) ? $this->convertElement($child) : $this->convertChildren($child)));

                continue;
            }

            $this->noteUnsupportedElement($child);
            $html .= $child->outerHTML;
        }

        array_push($blocks, ...$this->createTextBlocks($html));

        return $blocks;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertElement(Element $element): array
    {
        if ($element->classList->contains('gjs-text-ckeditor')) {
            return $this->convertChildren($element);
        }

        if ($element->localName === 'img') {
            return $this->convertImage($element, null);
        }

        if ($element->localName === 'a') {
            return $this->convertLink($element);
        }

        return $this->profile === ContentProfileEnum::MAIL ? $this->convertMailTable($element) : $this->convertWebElement($element);
    }

    private function isBlockElement(Element $element): bool
    {
        return $element->matches(self::BLOCK_SELECTORS[$this->profile]) || $this->isMappableElement($element);
    }

    private function containsBlockElement(Element $element): bool
    {
        if ($element->querySelector(self::BLOCK_SELECTORS[$this->profile]) !== null) {
            return true;
        }

        foreach ($element->querySelectorAll('img, a') as $descendant) {
            if ($this->isMappableElement($descendant)) {
                return true;
            }
        }

        return false;
    }

    private function noteUnsupportedElement(Element $element): void
    {
        if (!array_key_exists($element->localName, ContentHtmlSanitizer::ALLOWED_ELEMENTS) && !in_array($element->localName, self::PLAIN_ELEMENTS, true)) {
            $this->addNote(sprintf('<%s> is not supported, only its text was kept', $this->describe($element)));
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertWebElement(Element $element): array
    {
        if ($element->classList->contains('gjs-products')) {
            return $this->convertProducts($element);
        }

        if ($element->classList->contains('row')) {
            return $this->convertRow($element);
        }

        if ($element->classList->contains('gjs-text-with-image')) {
            return $this->convertTextWithImage($element);
        }

        $table = $element->classList->contains('gjs-table-custom') ? $element->querySelector('table') : null;

        if ($table !== null) {
            return $this->createTextBlocks($table->outerHTML);
        }

        if ($element->localName === 'iframe') {
            return $this->convertIframe($element);
        }

        return $this->convertUnknown($element);
    }

    private function isMappableElement(Element $element): bool
    {
        return $element->localName === 'img' || $this->isButton($element) || $this->isLinkedImage($element);
    }

    private function isButton(Element $element): bool
    {
        return $element->localName === 'a'
            && ($element->getAttribute('data-gjs-type') === 'button-link' || $element->classList->contains('gjs-button-link'));
    }

    private function isLinkedImage(Element $element): bool
    {
        return $element->localName === 'a' && $element->querySelector('img') !== null && !$this->isButton($element);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function createTextBlocks(string $html): array
    {
        $html = preg_replace('~<(/?)h1\b~i', '<$1h2', trim($html));

        if ($this->profile === ContentProfileEnum::MAIL) {
            $html = $this->convertLineBreaksToParagraphs($html);
        }

        $sanitizedHtml = $this->contentHtmlSanitizer->sanitize($html);

        if (trim(strip_tags($sanitizedHtml)) === '' && !str_contains($sanitizedHtml, '<hr')) {
            return [];
        }

        return [$this->createBlock(self::BLOCK_TYPES[$this->profile]['text'], ['html' => $sanitizedHtml])];
    }

    // mails written in GrapesJS separate paragraphs by two line breaks
    private function convertLineBreaksToParagraphs(string $html): string
    {
        $paragraphs = [];

        foreach (preg_split('~(?:\s*<br\s*/?>\s*){2,}~i', $html) as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph !== '') {
                $paragraphs[] = preg_match(self::BLOCK_ELEMENTS_PATTERN, $paragraph) === 1 ? $paragraph : '<p>' . $paragraph . '</p>';
            }
        }

        return implode('', $paragraphs);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertImage(Element $image, ?Element $link): array
    {
        $source = trim((string)$image->getAttribute('src'));
        $variable = $image->getAttribute('path') ?? $source;
        $alt = (string)$image->getAttribute('alt');

        if ($this->profile === ContentProfileEnum::MAIL && str_starts_with($variable, '{')) {
            return [$this->createBlock('mail-image-variable', ['variable' => $variable, 'alt' => $alt])];
        }

        $data = [
            'alt' => $alt,
            'position' => $this->getPosition($image, 'image-position-', 'data-image-position', ImageBlock::POSITION_CENTER),
            'link' => $link?->getAttribute('href') ?? '',
        ];
        $mediaId = $source === '' ? null : $this->legacyImageImporter->import($source);

        if ($mediaId !== null) {
            $data['mediaId'] = $mediaId;
        } elseif ($source !== '') {
            $data['url'] = $source;
            $this->addNote(sprintf('image "%s" is not in the content directory, only its url was kept', $source));
        } else {
            $this->addNote('image without a source was dropped');

            return [];
        }

        return [$this->createBlock(self::BLOCK_TYPES[$this->profile]['image'], $data)];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertLink(Element $link): array
    {
        if ($this->isButton($link)) {
            return $this->convertButton($link);
        }

        $image = $link->querySelector('img');

        if ($image !== null) {
            return $this->convertImage($image, $link);
        }

        return $this->convertUnknown($link);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertButton(Element $link): array
    {
        $text = trim(preg_replace('~\s+~', ' ', $link->textContent ?? '') ?? '');

        if ($text === '') {
            $this->addNote('button without a text was dropped');

            return [];
        }

        $data = [
            'text' => $text,
            'url' => (string)$link->getAttribute('href'),
            'position' => $this->getPosition($link, 'button-link-position-', 'data-link-position', ButtonLinkBlock::POSITION_CENTER),
        ];

        if ($this->profile === ContentProfileEnum::WEB) {
            $data['variant'] = ButtonLinkBlock::VARIANT_PRIMARY;
            $data['openInNewTab'] = $link->getAttribute('target') === '_blank';
        } else {
            $data['color'] = $this->getButtonColor($link);
        }

        return [$this->createBlock(self::BLOCK_TYPES[$this->profile]['button'], $data)];
    }

    private function getButtonColor(Element $link): string
    {
        $matches = [];

        if (preg_match('~background-color:\s*(#[0-9a-f]{3,6})\b~i', (string)$link->getAttribute('style'), $matches) === 1) {
            return $matches[1];
        }

        return MailButtonBlock::DEFAULT_COLOR;
    }

    private function getPosition(Element $element, string $classPrefix, string $attribute, string $default): string
    {
        foreach ([ImageBlock::POSITION_LEFT, ImageBlock::POSITION_CENTER, ImageBlock::POSITION_RIGHT] as $position) {
            if ($element->classList->contains($classPrefix . $position)) {
                return $position;
            }
        }

        return $element->getAttribute($attribute) ?? $default;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertProducts(Element $element): array
    {
        $catnums = array_filter(array_map('trim', explode(',', (string)$element->getAttribute('data-products'))));
        $productIds = $this->productRepository->getProductIdsByCatnums(array_values($catnums));

        if (count($productIds) < count($catnums)) {
            $this->addNote(sprintf('%d of %d catalog numbers of products are unknown and were dropped', count($catnums) - count($productIds), count($catnums)));
        }

        if ($productIds === []) {
            $this->addNote('products block without any known product was dropped');

            return [];
        }

        return [$this->createBlock('products', ['productIds' => $productIds])];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertRow(Element $row): array
    {
        $slots = [];

        foreach ($row->children as $column) {
            $slots[] = $this->convertChildren($column);
        }

        $type = [2 => 'columns-1-1', 3 => 'columns-1-1-1'][count($slots)] ?? null;

        if ($type !== null) {
            return [$this->createBlock($type, [], $slots)];
        }

        if (count($slots) > 3) {
            $this->addNote(sprintf('row with %d columns was converted to a sequence of blocks', count($slots)));
        }

        return array_merge(...$slots);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertTextWithImage(Element $element): array
    {
        $image = $element->querySelector('img');
        $text = $element->querySelector('.gjs-text-ckeditor');

        if ($image === null || $text === null) {
            return $this->convertUnknown($element);
        }

        $imageBlocks = $this->convertImage($image, null);
        $textBlocks = $this->convertChildren($text);

        if ($element->classList->contains('gjs-text-with-image-float-right')) {
            return [$this->createBlock('columns-2-1', [], [$textBlocks, $imageBlocks])];
        }

        return [$this->createBlock('columns-1-2', [], [$imageBlocks, $textBlocks])];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertIframe(Element $iframe): array
    {
        $url = trim((string)$iframe->getAttribute('src'));

        if ($url === '') {
            $this->addNote('iframe without a source was dropped');

            return [];
        }

        if ($this->videoUrlParser->parse($url) !== null) {
            return [$this->createBlock('video', ['url' => $url])];
        }

        if (str_contains($url, 'maps.google') || str_contains($url, 'google.com/maps')) {
            return [$this->createBlock('map', ['url' => $url])];
        }

        $matches = [];
        $height = preg_match('~height:\s*(\d+)px~i', (string)$iframe->getAttribute('style'), $matches) === 1
            ? (int)$matches[1]
            : IframeBlock::DEFAULT_HEIGHT;

        return [$this->createBlock('iframe', ['url' => $url, 'height' => $height])];
    }

    /**
     * The newsletter preset of GrapesJS makes a table with two cells (sect50) or with one cell (sect100)
     *
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertMailTable(Element $table): array
    {
        $rows = $table->querySelectorAll('tr');
        $cells = $rows->length === 1 ? $rows->item(0)->children : [];
        $slots = [];

        foreach ($cells as $cell) {
            $slots[] = $this->convertChildren($cell);
        }

        if (count($slots) === 2) {
            return [$this->createBlock('mail-columns', [], $slots)];
        }

        if (count($slots) === 1) {
            return $slots[0];
        }

        return $this->convertUnknown($table);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function convertUnknown(Element $element): array
    {
        $this->addNote(sprintf('<%s> has an unexpected structure and was kept as a text', $this->describe($element)));

        return $this->createTextBlocks($element->outerHTML);
    }

    private function describe(Element $element): string
    {
        return $element->localName . ($element->className !== '' ? '.' . $element->className : '');
    }

    /**
     * @param array<string, mixed> $data
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[][]|null $children
     */
    private function createBlock(string $type, array $data, ?array $children = null): ContentBlockInstance
    {
        return new ContentBlockInstance('', $type, $data, $children);
    }

    private function addNote(string $note): void
    {
        $this->notes[] = $note;
    }
}
