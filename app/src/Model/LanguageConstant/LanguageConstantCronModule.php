<?php

declare(strict_types=1);

namespace App\Model\LanguageConstant;

use GuzzleHttp\Exception\GuzzleException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class LanguageConstantCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private ?Logger $logger;

    /**
     * @var \App\Model\LanguageConstant\LanguageConstantFacade
     */
    private LanguageConstantFacade $languageConstantFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\LanguageConstant\LanguageConstantFacade $languageConstantFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(LanguageConstantFacade $languageConstantFacade, Domain $domain)
    {
        $this->languageConstantFacade = $languageConstantFacade;
        $this->domain = $domain;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function run()
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            try {
                $this->languageConstantFacade->generateLanguageConstantFile($locale);
            } catch (GuzzleException $exception) {
                $this->logger->addError(sprintf('Unable to load list of language constants for %s locale', $locale));
            }
        }
    }
}
