<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260208091423 extends AbstractMigration implements CdnAwareInterface
{
    protected const string CSP_FRAME_ANCESTORS_DIRECTIVE = "frame-ancestors 'self'";
    protected const string CSP_DEFAULT_SRC_VALUE = "'self' https: 'unsafe-inline' data:";
    protected const string CSP_SCRIPT_SRC_VALUE =
        "'self' 'unsafe-inline' https://www.googletagmanager.com *.usersnap.com https://widget.packeta.com https://cdnjs.cloudflare.com https://*.gopay.com https://*.gopay.cz https://maps.googleapis.com";

    protected ?CdnFacade $cdnFacade = null;

    #[Override]
    public function setCdnFacade(CdnFacade $cdnFacade): void
    {
        $this->cdnFacade = $cdnFacade;
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE setting_values SET value = :value WHERE name = :name AND domain_id = :domainId AND value = :oldValue', [
            'value' => $this->getDefaultCspHeaderValue(),
            'name' => 'cspHeader',
            'domainId' => 0,
            'oldValue' => "default-src https: 'unsafe-inline' 'unsafe-eval' data:",
        ]);
    }

    protected function getDefaultCspHeaderValue(): string
    {
        $cdnDomain = $this->cdnFacade?->getCdnDomain();
        $defaultSrcValue = self::CSP_DEFAULT_SRC_VALUE;
        $scriptSrcValue = self::CSP_SCRIPT_SRC_VALUE;

        if ($cdnDomain !== null) {
            $defaultSrcValue .= ' ' . $cdnDomain;
            $scriptSrcValue .= ' ' . $cdnDomain;
        }

        return self::CSP_FRAME_ANCESTORS_DIRECTIVE . '; default-src ' . $defaultSrcValue . '; script-src ' . $scriptSrcValue;
    }
}
