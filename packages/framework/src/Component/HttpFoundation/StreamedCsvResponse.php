<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

class StreamedCsvResponse extends StreamedResponse
{
    /**
     * Rows are encoded one by one so that large exports do not have to be held in memory,
     * the columns of the first row therefore define the columns of the whole export
     *
     * @param iterable<array<string, mixed>> $data
     */
    public function __construct(
        iterable $data,
        string $fileName,
        string $delimiter = ',',
        bool $withHeaderRow = true,
    ) {
        parent::__construct(static function () use ($data, $delimiter, $withHeaderRow): void {
            $csvEncoder = new CsvEncoder();
            $csvHeaders = null;

            foreach ($data as $row) {
                $isFirstRow = $csvHeaders === null;
                $csvHeaders ??= array_keys($row);

                echo $csvEncoder->encode([$row], CsvEncoder::FORMAT, [
                    CsvEncoder::DELIMITER_KEY => $delimiter,
                    CsvEncoder::ESCAPE_FORMULAS_KEY => true,
                    CsvEncoder::HEADERS_KEY => $csvHeaders,
                    CsvEncoder::NO_HEADERS_KEY => !$withHeaderRow || !$isFirstRow,
                ]);
            }
        });

        $this->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $this->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
