<?php

namespace Shopsys\FrameworkBundle\Model\Script;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Order\Order;

class ScriptFacade
{
    const VARIABLE_NUMBER = '{number}';
    const VARIABLE_TOTAL_PRICE = '{total_price}';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Script\ScriptRepository
     */
    protected $scriptRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Script\ScriptFactoryInterface
     */
    protected $scriptFactory;

    public function __construct(
        EntityManagerInterface $em,
        ScriptRepository $scriptRepository,
        Setting $setting,
        ScriptFactoryInterface $scriptFactory
    ) {
        $this->em = $em;
        $this->scriptRepository = $scriptRepository;
        $this->setting = $setting;
        $this->scriptFactory = $scriptFactory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Script\Script[]
     */
    public function getAll(): array
    {
        return $this->scriptRepository->getAll();
    }

    public function getAllQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->scriptRepository->getAllQueryBuilder();
    }
    
    public function getById(int $scriptId): \Shopsys\FrameworkBundle\Model\Script\Script
    {
        return $this->scriptRepository->getById($scriptId);
    }

    public function create(ScriptData $scriptData): \Shopsys\FrameworkBundle\Model\Script\Script
    {
        $script = $this->scriptFactory->create($scriptData);

        $this->em->persist($script);
        $this->em->flush();

        return $script;
    }
    
    public function edit(int $scriptId, ScriptData $scriptData): \Shopsys\FrameworkBundle\Model\Script\Script
    {
        $script = $this->scriptRepository->getById($scriptId);

        $script->edit($scriptData);

        $this->em->persist($script);
        $this->em->flush();

        return $script;
    }
    
    public function delete(int $scriptId): void
    {
        $script = $this->scriptRepository->getById($scriptId);

        $this->em->remove($script);
        $this->em->flush();
    }

    /**
     * @return string[]
     */
    public function getAllPagesScriptCodes(): array
    {
        $allPagesScripts = $this->scriptRepository->getScriptsByPlacement(Script::PLACEMENT_ALL_PAGES);
        $scriptCodes = [];

        foreach ($allPagesScripts as $script) {
            $scriptCodes[] = $script->getCode();
        }

        return $scriptCodes;
    }

    /**
     * @return string[]
     */
    public function getOrderSentPageScriptCodesWithReplacedVariables(Order $order): array
    {
        $scripts = $this->scriptRepository->getScriptsByPlacement(Script::PLACEMENT_ORDER_SENT_PAGE);
        $scriptCodes = [];

        foreach ($scripts as $script) {
            $scriptCodes[] = $this->replaceVariables($script->getCode(), $order);
        }

        return $scriptCodes;
    }

    public function isGoogleAnalyticsActivated($domainId): bool
    {
        return !empty($this->setting->getForDomain(Script::GOOGLE_ANALYTICS_TRACKING_ID_SETTING_NAME, $domainId));
    }

    public function setGoogleAnalyticsTrackingId(?string $trackingId, int $domainId): void
    {
        $this->setting->setForDomain(Script::GOOGLE_ANALYTICS_TRACKING_ID_SETTING_NAME, $trackingId, $domainId);
    }

    public function getGoogleAnalyticsTrackingId($domainId): ?string
    {
        return $this->setting->getForDomain(Script::GOOGLE_ANALYTICS_TRACKING_ID_SETTING_NAME, $domainId);
    }
    
    protected function replaceVariables(string $code, Order $order): string
    {
        $variableReplacements = [
            self::VARIABLE_NUMBER => $order->getNumber(),
            self::VARIABLE_TOTAL_PRICE => $order->getTotalPriceWithVat(),
        ];

        return strtr($code, $variableReplacements);
    }
}
