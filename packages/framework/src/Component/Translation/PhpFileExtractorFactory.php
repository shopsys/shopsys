<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

class PhpFileExtractorFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Translation\PhpParserNodeHelper $phpParserNodeHelper
     */
    public function __construct(
        protected readonly PhpParserNodeHelper $phpParserNodeHelper,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Translation\PhpFileExtractor
     */
    public function create()
    {
        $transMethodSpecifications = [
            new TransMethodSpecification('trans', 0, 2),
            new TransMethodSpecification('t', 0, 2),
        ];

        return new PhpFileExtractor($this->phpParserNodeHelper, $transMethodSpecifications);
    }
}
