<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

class CsvResponse extends Response
{
    /**
     * @param iterable<array-key, array<string|int, mixed>> $data
     * @param array<int, string>|null $csvHeaders
     * @param array<string, mixed> $context
     */
    public function __construct(iterable $data, string $fileName, ?array $csvHeaders = null, array $context = [])
    {
        $csvEncoder = new CsvEncoder();

        if ($csvHeaders !== null) {
            $context[CsvEncoder::HEADERS_KEY] = $csvHeaders;
        }

        $context[CsvEncoder::ESCAPE_FORMULAS_KEY] = true;

        $content = $csvEncoder->encode($data, CsvEncoder::FORMAT, $context);

        parent::__construct($content);

        $this->headers->set('Content-Type', 'text/csv');
        $this->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
