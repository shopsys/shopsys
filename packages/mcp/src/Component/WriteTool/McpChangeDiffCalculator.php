<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\WriteTool;

class McpChangeDiffCalculator
{
    public const string SEGMENT_EQUAL = 'equal';
    public const string SEGMENT_DELETED = 'deleted';
    public const string SEGMENT_INSERTED = 'inserted';

    protected const int MAX_COMPARED_TOKEN_PAIRS = 250000;
    protected const float MIN_EQUAL_RATIO = 0.4;
    protected const int MIN_KEPT_EQUAL_WORD_COUNT = 2;

    /**
     * @return array<int, array{type: string, text: string}>
     */
    public function calculate(?string $before, ?string $after): array
    {
        $before ??= '';
        $after ??= '';
        $beforeTokens = $this->tokenize($before);
        $afterTokens = $this->tokenize($after);

        if (count($beforeTokens) * count($afterTokens) > static::MAX_COMPARED_TOKEN_PAIRS) {
            return $this->createReplacementSegments($before, $after);
        }

        $segments = $this->mergeSegments($this->diffTokens($beforeTokens, $afterTokens));

        if ($this->getEqualRatio($segments, $before, $after) < static::MIN_EQUAL_RATIO) {
            return $this->createReplacementSegments($before, $after);
        }

        return $this->absorbShortEqualSegments($segments);
    }

    /**
     * @return array<int, array{type: string, text: string}>
     */
    protected function createReplacementSegments(string $before, string $after): array
    {
        $segments = [
            ['type' => static::SEGMENT_DELETED, 'text' => $before],
            ['type' => static::SEGMENT_INSERTED, 'text' => $after],
        ];

        return array_values(array_filter($segments, static fn (array $segment) => $segment['text'] !== ''));
    }

    /**
     * @param array<int, array{type: string, text: string}> $segments
     */
    protected function getEqualRatio(array $segments, string $before, string $after): float
    {
        $longerLength = max(mb_strlen($before), mb_strlen($after));

        if ($longerLength === 0) {
            return 1.0;
        }

        $equalLength = 0;

        foreach ($segments as $segment) {
            if ($segment['type'] === static::SEGMENT_EQUAL) {
                $equalLength += mb_strlen($segment['text']);
            }
        }

        return $equalLength / $longerLength;
    }

    /**
     * @param array<int, array{type: string, text: string}> $segments
     * @return array<int, array{type: string, text: string}>
     */
    protected function absorbShortEqualSegments(array $segments): array
    {
        $absorbedSegments = [];
        $deletedText = '';
        $insertedText = '';
        $lastIndex = count($segments) - 1;

        foreach ($segments as $index => $segment) {
            $isKeptEqualSegment = $segment['type'] === static::SEGMENT_EQUAL
                && ($index === 0 || $index === $lastIndex || $this->countWords($segment['text']) >= static::MIN_KEPT_EQUAL_WORD_COUNT);

            if ($isKeptEqualSegment) {
                $absorbedSegments = [...$absorbedSegments, ...$this->createReplacementSegments($deletedText, $insertedText), $segment];
                $deletedText = '';
                $insertedText = '';

                continue;
            }

            if ($segment['type'] !== static::SEGMENT_INSERTED) {
                $deletedText .= $segment['text'];
            }

            if ($segment['type'] !== static::SEGMENT_DELETED) {
                $insertedText .= $segment['text'];
            }
        }

        return [...$absorbedSegments, ...$this->createReplacementSegments($deletedText, $insertedText)];
    }

    protected function countWords(string $text): int
    {
        return count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);
    }

    /**
     * @return string[]
     */
    protected function tokenize(string $text): array
    {
        if ($text === '') {
            return [];
        }

        return preg_split('/(\s+|[<>])/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [$text];
    }

    /**
     * @param string[] $beforeTokens
     * @param string[] $afterTokens
     * @return array<int, array{type: string, text: string}>
     */
    protected function diffTokens(array $beforeTokens, array $afterTokens): array
    {
        $beforeCount = count($beforeTokens);
        $afterCount = count($afterTokens);
        $commonLengths = array_fill(0, $beforeCount + 1, array_fill(0, $afterCount + 1, 0));

        for ($i = $beforeCount - 1; $i >= 0; $i--) {
            for ($j = $afterCount - 1; $j >= 0; $j--) {
                $commonLengths[$i][$j] = $beforeTokens[$i] === $afterTokens[$j]
                    ? $commonLengths[$i + 1][$j + 1] + 1
                    : max($commonLengths[$i + 1][$j], $commonLengths[$i][$j + 1]);
            }
        }

        $segments = [];
        $i = 0;
        $j = 0;

        while ($i < $beforeCount && $j < $afterCount) {
            if ($beforeTokens[$i] === $afterTokens[$j]) {
                $segments[] = ['type' => static::SEGMENT_EQUAL, 'text' => $beforeTokens[$i]];
                $i++;
                $j++;
            } elseif ($commonLengths[$i + 1][$j] >= $commonLengths[$i][$j + 1]) {
                $segments[] = ['type' => static::SEGMENT_DELETED, 'text' => $beforeTokens[$i]];
                $i++;
            } else {
                $segments[] = ['type' => static::SEGMENT_INSERTED, 'text' => $afterTokens[$j]];
                $j++;
            }
        }

        return [
            ...$segments,
            ...$this->createSegments(static::SEGMENT_DELETED, array_slice($beforeTokens, $i)),
            ...$this->createSegments(static::SEGMENT_INSERTED, array_slice($afterTokens, $j)),
        ];
    }

    /**
     * @param string[] $tokens
     * @return array<int, array{type: string, text: string}>
     */
    protected function createSegments(string $type, array $tokens): array
    {
        return array_map(static fn (string $token) => ['type' => $type, 'text' => $token], $tokens);
    }

    /**
     * @param array<int, array{type: string, text: string}> $segments
     * @return array<int, array{type: string, text: string}>
     */
    protected function mergeSegments(array $segments): array
    {
        $mergedSegments = [];

        foreach ($segments as $segment) {
            $lastIndex = count($mergedSegments) - 1;

            if ($lastIndex >= 0 && $mergedSegments[$lastIndex]['type'] === $segment['type']) {
                $mergedSegments[$lastIndex]['text'] .= $segment['text'];
            } else {
                $mergedSegments[] = $segment;
            }
        }

        return $mergedSegments;
    }
}
