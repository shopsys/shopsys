<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class TransportData
{
    /**
     * @var string[]
     */
    public $name;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public $vat;

    /**
     * @var string[]
     */
    public $description;

    /**
     * @var string[]
     */
    public $instructions;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var string
     */
    public $image;

    /**
     * @var int[]
     */
    public $domains;

    /**
     * @param string[] $names
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null $vat
     * @param string[] $descriptions
     * @param string[] $instructions
     * @param bool $hidden
     * @param int[] $domains
     */
    public function __construct(
        array $names = [],
        Vat $vat = null,
        array $descriptions = [],
        array $instructions = [],
        $hidden = false,
        array $domains = []
    ) {
        $this->name = $names;
        $this->vat = $vat;
        $this->description = $descriptions;
        $this->instructions = $instructions;
        $this->hidden = $hidden;
        $this->domains = $domains;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportDomain[] $transportDomains
     */
    public function setFromEntity(Transport $transport, array $transportDomains)
    {
        $translations = $transport->getTranslations();
        $names = [];
        $descriptions = [];
        $instructions = [];
        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
            $descriptions[$translate->getLocale()] = $translate->getDescription();
            $instructions[$translate->getLocale()] = $translate->getInstructions();
        }
        $this->name = $names;
        $this->description = $descriptions;
        $this->instructions = $instructions;
        $this->hidden = $transport->isHidden();
        $this->vat = $transport->getVat();

        $domains = [];
        foreach ($transportDomains as $transportDomain) {
            $domains[] = $transportDomain->getDomainId();
        }
        $this->domains = $domains;
    }
}
