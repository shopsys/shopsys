<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Shopsys\FrameworkBundle\Component\Xml\XmlNormalizerHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class XmlResponse extends Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        protected readonly XmlNormalizerHelper $xmlNormalizerHelper,
        ?string $content = '',
        int $status = 200,
        array $headers = [],
    ) {
        parent::__construct($content, $status, $headers);
    }

    public function getXmlResponse(string $fileName, string $content): Response
    {
        $xmlContent = $this->xmlNormalizerHelper->normalizeXml($content);
        $response = new Response($xmlContent);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName,
        );

        $response->headers->set('Content-Type', 'xml');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
