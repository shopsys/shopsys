<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use FPDF;

class GiftVoucherPdfDocument extends FPDF
{
    protected const string FONT_SANS = 'dejavusans';
    protected const string FONT_MONO = 'dejavusansmono';

    protected const float BASELINE_RATIO = 0.905;
    protected const float LINE_HEIGHT_RATIO = 1.2;
    protected const float VERTICAL_SCALE = 1.12;

    protected const array COLOR_TITLE = [255, 255, 255];
    protected const array COLOR_TITLE_UNDERLINE = [2, 202, 192];
    protected const array COLOR_VALUE_LABEL = [201, 205, 242];
    protected const array COLOR_RING = [42, 28, 178];
    protected const array COLOR_CONTENT_PANEL = [251, 251, 253];
    protected const array COLOR_SHOP_NAME = [85, 84, 106];
    protected const array COLOR_SHOP_URL = [117, 116, 138];
    protected const array COLOR_CODE_BOX = [237, 239, 250];
    protected const array COLOR_CODE_LABEL = [30, 159, 242];
    protected const array COLOR_CODE = [13, 4, 156];
    protected const array COLOR_ACCENT = [11, 1, 144];
    protected const array COLOR_VALIDITY_DATE = [16, 16, 31];
    protected const array COLOR_MUTED = [95, 96, 117];
    protected const array COLOR_CONDITIONS_SEPARATOR = [229, 229, 237];

    public function __construct(protected readonly string $fontDir)
    {
        parent::__construct('L', 'pt', 'A5');

        $this->SetAutoPageBreak(false);
        $this->SetMargins(0, 0, 0);
        $this->AddFont(self::FONT_SANS, '', 'DejaVuSans.json', $this->fontDir);
        $this->AddFont(self::FONT_SANS, 'B', 'DejaVuSans-Bold.json', $this->fontDir);
        $this->AddFont(self::FONT_MONO, 'B', 'DejaVuSansMono-Bold.json', $this->fontDir);
        $this->AddPage();
    }

    /**
     * @param array{
     *     title: string,
     *     valueLabel: string,
     *     valuePrefix: string|null,
     *     valueMain: string,
     *     valueSuffix: string|null,
     *     backgroundFilepath: string,
     *     logoFilepath: string|null,
     *     shopName: string,
     *     shopUrl: string,
     *     codeLabel: string,
     *     codeChunks: string[],
     *     validityLabel: string,
     *     validityDate: string,
     *     redemptionLabel: string,
     *     redemptionText: string,
     *     conditionsTitle: string,
     *     conditionsText: string,
     * } $voucherData
     */
    public function renderVoucher(array $voucherData): void
    {
        $this->Image($voucherData['backgroundFilepath'], 0, 0, 240, 420, 'PNG');
        $this->fillContentPanel();
        $this->drawSeamNotches();
        $this->drawDecorativeRings();

        $this->drawTitle($voucherData);
        $this->drawValue($voucherData);
        $this->drawHeader($voucherData);
        $this->drawCodeBox($voucherData);
        $this->drawValidity($voucherData);
        $this->drawRedemption($voucherData);
        $this->drawConditions($voucherData);
    }

    /**
     * @param array<string, mixed> $voucherData
     */
    protected function drawTitle(array $voucherData): void
    {
        $this->setTextColorFrom(self::COLOR_TITLE);
        $this->drawSpacedText(34.0, $this->baseline(34.0, 12.0), $this->upper($voucherData['title']), self::FONT_SANS, 12.0, 2.5);

        $this->setDrawColorFrom(self::COLOR_TITLE_UNDERLINE);
        $this->SetLineWidth(2.0);
        $this->Line(34.0, 63.0, 72.0, 63.0);
    }

    /**
     * @param array<string, mixed> $voucherData
     */
    protected function drawValue(array $voucherData): void
    {
        $this->setTextColorFrom(self::COLOR_VALUE_LABEL);
        $this->SetFont(self::FONT_SANS, 'B', 9.5);
        $this->Text(34.0, $this->baseline(192.0, 9.5) - 8.6, $this->encode($voucherData['valueLabel']));

        $this->setTextColorFrom(self::COLOR_TITLE);
        $baseline = $this->baseline(197.0, 42.0) - 8.6;
        $positionX = 34.0;

        if ($voucherData['valuePrefix'] !== null) {
            $this->SetFont(self::FONT_SANS, 'B', 18.0);
            $prefix = $this->encode($voucherData['valuePrefix']) . ' ';
            $this->Text($positionX, $baseline, $prefix);
            $positionX += $this->GetStringWidth($prefix);
        }

        $this->SetFont(self::FONT_SANS, 'B', 42.0);
        $this->Text($positionX, $baseline, $this->encode($voucherData['valueMain']));
        $positionX += $this->GetStringWidth($this->encode($voucherData['valueMain']));

        if ($voucherData['valueSuffix'] === null) {
            return;
        }

        $this->SetFont(self::FONT_SANS, 'B', 18.0);
        $this->Text($positionX, $baseline, ' ' . $this->encode($voucherData['valueSuffix']));
    }

    /**
     * @param array<string, mixed> $voucherData
     */
    protected function drawHeader(array $voucherData): void
    {
        if ($voucherData['logoFilepath'] !== null && is_file($voucherData['logoFilepath'])) {
            $this->Image($voucherData['logoFilepath'], 256.0, 30.0, 0, 28.0, 'PNG');
        } else {
            $this->setTextColorFrom(self::COLOR_SHOP_NAME);
            $this->SetFont(self::FONT_SANS, 'B', 12.0);
            $this->Text(256.0, $this->baseline(30.0, 12.0), $this->encode($voucherData['shopName']));
        }

        $this->setTextColorFrom(self::COLOR_SHOP_NAME);
        $this->SetFont(self::FONT_SANS, '', 8.0);
        $this->drawRightAlignedText(566.0, $this->baseline(30.0, 8.0), $voucherData['shopName']);

        $this->setTextColorFrom(self::COLOR_SHOP_URL);
        $this->drawRightAlignedText(566.0, $this->baseline(30.0 + 8.0 * self::LINE_HEIGHT_RATIO, 8.0), $voucherData['shopUrl']);
    }

    /**
     * @param array<string, mixed> $voucherData
     */
    protected function drawCodeBox(array $voucherData): void
    {
        $codeTop = 91.0 + 8.0 * self::LINE_HEIGHT_RATIO + 10.0;
        $boxTop = 73.0 * self::VERTICAL_SCALE;
        $boxBottom = $this->baseline($codeTop, 24.0) + 23.0;

        $this->setFillColorFrom(self::COLOR_CODE_BOX);
        $this->roundedRect(256.0, $boxTop, 310.0, $boxBottom - $boxTop, 14.0, 'F');

        $this->setTextColorFrom(self::COLOR_CODE_LABEL);
        $this->drawSpacedTextCentered(411.0, $this->baseline(91.0, 8.0), $this->upper($voucherData['codeLabel']), self::FONT_SANS, 8.0, 2.0);

        $this->setTextColorFrom(self::COLOR_CODE);
        $this->drawSpacedTextCentered(411.0, $this->baseline($codeTop, 24.0), implode(' ', $voucherData['codeChunks']), self::FONT_MONO, 24.0, 4.0);
    }

    /**
     * @param array<string, mixed> $voucherData
     */
    protected function drawValidity(array $voucherData): void
    {
        $label = $this->encode($voucherData['validityLabel'] . ':');
        $date = $this->encode($voucherData['validityDate']);

        $this->SetFont(self::FONT_SANS, 'B', 10.0);
        $gap = 8.0;
        $labelWidth = $this->GetStringWidth($label);
        $totalWidth = $labelWidth + $gap + $this->GetStringWidth($date);
        $positionX = 411.0 - $totalWidth / 2.0;
        $baseline = $this->baseline(169.0, 10.0);

        $this->setTextColorFrom(self::COLOR_ACCENT);
        $this->Text($positionX, $baseline, $label);

        $this->setTextColorFrom(self::COLOR_VALIDITY_DATE);
        $this->Text($positionX + $labelWidth + $gap, $baseline, $date);
    }

    /**
     * @param array<string, mixed> $voucherData
     */
    protected function drawRedemption(array $voucherData): void
    {
        $segments = [
            ['text' => $voucherData['redemptionLabel'], 'bold' => true],
            ['text' => ' ' . $voucherData['redemptionText'], 'bold' => false],
        ];
        $this->drawCenteredRichText($segments, 256.0, 310.0, 199.0, 9.0, self::COLOR_ACCENT, self::COLOR_MUTED);
    }

    /**
     * @param array<string, mixed> $voucherData
     */
    protected function drawConditions(array $voucherData): void
    {
        $this->setDrawColorFrom(self::COLOR_CONDITIONS_SEPARATOR);
        $this->SetLineWidth(1.0);
        $this->Line(256.0, 282.0, 566.0, 282.0);

        $this->setTextColorFrom(self::COLOR_ACCENT);
        $this->drawSpacedText(256.0, 310.0, $this->upper($voucherData['conditionsTitle']), self::FONT_SANS, 8.5, 2.0);

        $this->setTextColorFrom(self::COLOR_MUTED);
        $this->SetFont(self::FONT_SANS, '', 8.5);
        $this->SetXY(256.0, 318.0);
        $this->MultiCell(310.0, 14.5, $this->encode($voucherData['conditionsText']), 0, 'L');
    }

    protected function fillContentPanel(): void
    {
        $this->setFillColorFrom(self::COLOR_CONTENT_PANEL);
        $this->Rect(226.0, 0.0, 370.0, 420.0, 'F');
    }

    protected function drawSeamNotches(): void
    {
        $this->setFillColorFrom(self::COLOR_CONTENT_PANEL);
        $this->cornerNotch(214.0, 0.0, 12.0, 'BL');
        $this->cornerNotch(214.0, 408.0, 12.0, 'TL');
    }

    protected function drawDecorativeRings(): void
    {
        $this->setDrawColorFrom(self::COLOR_RING);
        $this->SetLineWidth(2.0);
        $this->circle(35.0, 385.0, 85.0);
        $this->circle(35.0, 385.0, 55.0);
    }

    protected function baseline(float $top, float $size): float
    {
        return $top * self::VERTICAL_SCALE + $size * self::BASELINE_RATIO;
    }

    protected function upper(string $text): string
    {
        return mb_strtoupper($text, 'UTF-8');
    }

    protected function drawRightAlignedText(float $rightX, float $baseline, string $text): void
    {
        $encoded = $this->encode($text);
        $this->Text($rightX - $this->GetStringWidth($encoded), $baseline, $encoded);
    }

    /**
     * @param array<int, array{text: string, bold: bool}> $segments
     * @param int[] $boldColor
     * @param int[] $regularColor
     */
    protected function drawCenteredRichText(
        array $segments,
        float $left,
        float $width,
        float $top,
        float $size,
        array $boldColor,
        array $regularColor,
    ): void {
        $words = [];

        foreach ($segments as $segment) {
            foreach ($this->splitToWords($segment['text']) as $word) {
                $words[] = ['text' => $word, 'bold' => $segment['bold']];
            }
        }

        $lines = $this->wrapWords($words, $width, $size);
        $lineHeight = $size * self::LINE_HEIGHT_RATIO;

        foreach ($lines as $lineIndex => $line) {
            $baseline = $this->baseline($top + $lineIndex * $lineHeight, $size);
            $positionX = $left + ($width - $this->lineWidth($line, $size)) / 2.0;

            foreach ($line as $word) {
                $this->SetFont(self::FONT_SANS, $word['bold'] ? 'B' : '', $size);
                $this->setTextColorFrom($word['bold'] ? $boldColor : $regularColor);
                $encoded = $this->encode($word['text']);
                $this->Text($positionX, $baseline, $encoded);
                $positionX += $this->GetStringWidth($encoded);
            }
        }
    }

    /**
     * @return string[]
     */
    protected function splitToWords(string $text): array
    {
        $words = [];
        $length = mb_strlen($text);
        $current = '';

        for ($index = 0; $index < $length; $index++) {
            $character = mb_substr($text, $index, 1);

            if ($character === ' ') {
                $current .= ' ';
                $words[] = $current;
                $current = '';

                continue;
            }

            $current .= $character;
        }

        if ($current !== '') {
            $words[] = $current;
        }

        return $words;
    }

    /**
     * @param array<int, array{text: string, bold: bool}> $words
     * @return array<int, array<int, array{text: string, bold: bool}>>
     */
    protected function wrapWords(array $words, float $width, float $size): array
    {
        $lines = [];
        $currentLine = [];

        foreach ($words as $word) {
            $candidate = array_merge($currentLine, [$word]);

            if ($currentLine !== [] && $this->lineWidth($this->trimTrailingSpace($candidate), $size) > $width) {
                $lines[] = $this->trimTrailingSpace($currentLine);
                $currentLine = [$word];

                continue;
            }

            $currentLine = $candidate;
        }

        if ($currentLine !== []) {
            $lines[] = $this->trimTrailingSpace($currentLine);
        }

        return $lines;
    }

    /**
     * @param array<int, array{text: string, bold: bool}> $line
     * @return array<int, array{text: string, bold: bool}>
     */
    protected function trimTrailingSpace(array $line): array
    {
        if ($line === []) {
            return $line;
        }

        $lastIndex = array_key_last($line);
        $line[$lastIndex]['text'] = rtrim($line[$lastIndex]['text']);

        return $line;
    }

    /**
     * @param array<int, array{text: string, bold: bool}> $line
     */
    protected function lineWidth(array $line, float $size): float
    {
        $width = 0.0;

        foreach ($line as $word) {
            $this->SetFont(self::FONT_SANS, $word['bold'] ? 'B' : '', $size);
            $width += $this->GetStringWidth($this->encode($word['text']));
        }

        return $width;
    }

    protected function drawSpacedTextCentered(
        float $centerX,
        float $baseline,
        string $text,
        string $family,
        float $size,
        float $letterSpacing,
    ): void {
        $totalWidth = $this->spacedTextWidth($text, $family, $size, $letterSpacing);
        $this->drawSpacedText($centerX - $totalWidth / 2.0, $baseline, $text, $family, $size, $letterSpacing);
    }

    protected function drawSpacedText(
        float $startX,
        float $baseline,
        string $text,
        string $family,
        float $size,
        float $letterSpacing,
    ): void {
        $this->SetFont($family, 'B', $size);
        $positionX = $startX;

        foreach ($this->splitToCharacters($text) as $character) {
            $encoded = $this->encode($character);
            $this->Text($positionX, $baseline, $encoded);
            $positionX += $this->GetStringWidth($encoded) + $letterSpacing;
        }
    }

    protected function spacedTextWidth(string $text, string $family, float $size, float $letterSpacing): float
    {
        $this->SetFont($family, 'B', $size);
        $characters = $this->splitToCharacters($text);
        $width = 0.0;

        foreach ($characters as $character) {
            $width += $this->GetStringWidth($this->encode($character)) + $letterSpacing;
        }

        return $width - $letterSpacing;
    }

    /**
     * @return string[]
     */
    protected function splitToCharacters(string $text): array
    {
        return mb_str_split($text);
    }

    protected function encode(string $text): string
    {
        return (string)iconv('UTF-8', 'CP1250//TRANSLIT', $text);
    }

    /**
     * @param int[] $color
     */
    protected function setTextColorFrom(array $color): void
    {
        $this->SetTextColor($color[0], $color[1], $color[2]);
    }

    /**
     * @param int[] $color
     */
    protected function setDrawColorFrom(array $color): void
    {
        $this->SetDrawColor($color[0], $color[1], $color[2]);
    }

    /**
     * @param int[] $color
     */
    protected function setFillColorFrom(array $color): void
    {
        $this->SetFillColor($color[0], $color[1], $color[2]);
    }

    protected function cornerNotch(float $x, float $y, float $size, string $roundedCorner): void
    {
        $scale = $this->k;
        $pageHeight = $this->h;
        $control = 0.5522847498 * $size;

        if ($roundedCorner === 'BL') {
            $this->_out(sprintf('%.2F %.2F m', $x * $scale, ($pageHeight - $y) * $scale));
            $this->_out(sprintf('%.2F %.2F l', ($x + $size) * $scale, ($pageHeight - $y) * $scale));
            $this->_out(sprintf('%.2F %.2F l', ($x + $size) * $scale, ($pageHeight - ($y + $size)) * $scale));
            $this->pathArc($x + $size - $control, $y + $size, $x, $y + $control, $x, $y);
        } else {
            $this->_out(sprintf('%.2F %.2F m', ($x + $size) * $scale, ($pageHeight - $y) * $scale));
            $this->_out(sprintf('%.2F %.2F l', ($x + $size) * $scale, ($pageHeight - ($y + $size)) * $scale));
            $this->_out(sprintf('%.2F %.2F l', $x * $scale, ($pageHeight - ($y + $size)) * $scale));
            $this->pathArc($x, $y + $size - $control, $x + $size - $control, $y, $x + $size, $y);
        }

        $this->_out('f');
    }

    protected function roundedRect(float $x, float $y, float $width, float $height, float $radius, string $style): void
    {
        $operation = $style === 'F' ? 'f' : ($style === 'FD' || $style === 'DF' ? 'B' : 'S');
        $bezierFactor = 4.0 / 3.0 * (M_SQRT2 - 1.0);
        $scale = $this->k;
        $pageHeight = $this->h;

        $this->_out(sprintf('%.2F %.2F m', ($x + $radius) * $scale, ($pageHeight - $y) * $scale));

        $arcX = $x + $width - $radius;
        $arcY = $y + $radius;
        $this->_out(sprintf('%.2F %.2F l', $arcX * $scale, ($pageHeight - $y) * $scale));
        $this->roundedRectArc($arcX + $radius * $bezierFactor, $arcY - $radius, $arcX + $radius, $arcY - $radius * $bezierFactor, $arcX + $radius, $arcY);

        $arcX = $x + $width - $radius;
        $arcY = $y + $height - $radius;
        $this->_out(sprintf('%.2F %.2F l', ($x + $width) * $scale, ($pageHeight - $arcY) * $scale));
        $this->roundedRectArc($arcX + $radius, $arcY + $radius * $bezierFactor, $arcX + $radius * $bezierFactor, $arcY + $radius, $arcX, $arcY + $radius);

        $arcX = $x + $radius;
        $arcY = $y + $height - $radius;
        $this->_out(sprintf('%.2F %.2F l', $arcX * $scale, ($pageHeight - ($y + $height)) * $scale));
        $this->roundedRectArc($arcX - $radius * $bezierFactor, $arcY + $radius, $arcX - $radius, $arcY + $radius * $bezierFactor, $arcX - $radius, $arcY);

        $arcX = $x + $radius;
        $arcY = $y + $radius;
        $this->_out(sprintf('%.2F %.2F l', $x * $scale, ($pageHeight - $arcY) * $scale));
        $this->roundedRectArc($arcX - $radius, $arcY - $radius * $bezierFactor, $arcX - $radius * $bezierFactor, $arcY - $radius, $arcX, $arcY - $radius);

        $this->_out($operation);
    }

    protected function roundedRectArc(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): void
    {
        $this->pathArc($x1, $y1, $x2, $y2, $x3, $y3);
    }

    protected function pathArc(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): void
    {
        $scale = $this->k;
        $pageHeight = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $scale,
            ($pageHeight - $y1) * $scale,
            $x2 * $scale,
            ($pageHeight - $y2) * $scale,
            $x3 * $scale,
            ($pageHeight - $y3) * $scale,
        ));
    }

    protected function circle(float $centerX, float $centerY, float $radius): void
    {
        $bezierOffset = 4.0 / 3.0 * (M_SQRT2 - 1.0) * $radius;
        $scale = $this->k;
        $pageHeight = $this->h;

        $this->_out(sprintf('%.2F %.2F m', ($centerX + $radius) * $scale, ($pageHeight - $centerY) * $scale));
        $this->pathArc($centerX + $radius, $centerY - $bezierOffset, $centerX + $bezierOffset, $centerY - $radius, $centerX, $centerY - $radius);
        $this->pathArc($centerX - $bezierOffset, $centerY - $radius, $centerX - $radius, $centerY - $bezierOffset, $centerX - $radius, $centerY);
        $this->pathArc($centerX - $radius, $centerY + $bezierOffset, $centerX - $bezierOffset, $centerY + $radius, $centerX, $centerY + $radius);
        $this->pathArc($centerX + $bezierOffset, $centerY + $radius, $centerX + $radius, $centerY + $bezierOffset, $centerX + $radius, $centerY);
        $this->_out('S');
    }
}
