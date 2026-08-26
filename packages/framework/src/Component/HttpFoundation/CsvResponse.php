<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

class CsvResponse extends Response
{
    /**
     * @param array<int, array<string, mixed>> $data
     * @param array<array-key, string>|null $csvHeaders
     */
    public function __construct(array $data, string $fileName, ?array $csvHeaders = null)
    {
        $csvEncoder = new CsvEncoder();
        $context = [];

        if ($csvHeaders !== null) {
            $context[CsvEncoder::HEADERS_KEY] = $csvHeaders;
        }

        $content = $csvEncoder->encode($data, CsvEncoder::FORMAT, $context);

        parent::__construct($content);

        $this->headers->set('Content-Type', 'text/csv');
        $this->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
