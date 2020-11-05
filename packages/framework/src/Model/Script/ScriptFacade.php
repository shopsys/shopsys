<?php

namespace Shopsys\FrameworkBundle\Model\Script;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Twig\MoneyExtension;

class ScriptFacade
{
    public const VARIABLE_NUMBER = '{number}';
    public const VARIABLE_TOTAL_PRICE = '{total_price}';

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

    /**
     * @var \Shopsys\FrameworkBundle\Twig\MoneyExtension
     */
    protected $moneyExtension;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptRepository $scriptRepository
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptFactoryInterface $scriptFactory
     * @param \Shopsys\FrameworkBundle\Twig\MoneyExtension $moneyExtension
     */
    public function __construct(
        EntityManagerInterface $em,
        ScriptRepository $scriptRepository,
        Setting $setting,
        ScriptFactoryInterface $scriptFactory,
        MoneyExtension $moneyExtension
    ) {
        $this->em = $em;
        $this->scriptRepository = $scriptRepository;
        $this->setting = $setting;
        $this->scriptFactory = $scriptFactory;
        $this->moneyExtension = $moneyExtension;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Script\Script[]
     */
    public function getAll()
    {
        return $this->scriptRepository->getAll();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder()
    {
        return $this->scriptRepository->getAllQueryBuilder();
    }

    /**
     * @param int $scriptId
     * @return \Shopsys\FrameworkBundle\Model\Script\Script
     */
    public function getById($scriptId)
    {
        return $this->scriptRepository->getById($scriptId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptData $scriptData
     * @return \Shopsys\FrameworkBundle\Model\Script\Script
     */
    public function create(ScriptData $scriptData)
    {
        $script = $this->scriptFactory->create($scriptData);

        $this->em->persist($script);
        $this->em->flush();

        return $script;
    }

    /**
     * @param int $scriptId
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptData $scriptData
     * @return \Shopsys\FrameworkBundle\Model\Script\Script
     */
    public function edit($scriptId, ScriptData $scriptData)
    {
        $script = $this->scriptRepository->getById($scriptId);

        $script->edit($scriptData);

        $this->em->persist($script);
        $this->em->flush();

        return $script;
    }

    /**
     * @param int $scriptId
     */
    public function delete($scriptId)
    {
        $script = $this->scriptRepository->getById($scriptId);

        $this->em->remove($script);
        $this->em->flush();
    }

    /**
     * @return string[]
     * @deprecated use getAllPagesBeforeContentScriptCodes() instead
     */
    public function getAllPagesScriptCodes()
    {
        @trigger_error(
            sprintf(
                'The "%s()" method is deprecated and will be removed in the next major. Use "getAllPagesBeforeContentScriptCodes()" instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return $this->getAllPagesBeforeContentScriptCodes();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string[]
     */
    public function getOrderSentPageScriptCodesWithReplacedVariables(Order $order)
    {
        $scriptCodes = $this->getScriptCodesByPlacement(Script::PLACEMENT_ORDER_SENT_PAGE);

        $scriptCodesWithReplacedVariables = [];
        foreach ($scriptCodes as $scriptCode) {
            $scriptCodesWithReplacedVariables[] = $this->replaceVariables($scriptCode, $order);
        }

        return $scriptCodesWithReplacedVariables;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isGoogleAnalyticsActivated($domainId)
    {
        return $this->setting->getForDomain(Script::GOOGLE_ANALYTICS_TRACKING_ID_SETTING_NAME, $domainId) !== null;
    }

    /**
     * @param string|null $trackingId
     * @param int $domainId
     */
    public function setGoogleAnalyticsTrackingId($trackingId, $domainId)
    {
        $this->setting->setForDomain(Script::GOOGLE_ANALYTICS_TRACKING_ID_SETTING_NAME, $trackingId, $domainId);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getGoogleAnalyticsTrackingId($domainId)
    {
        return $this->setting->getForDomain(Script::GOOGLE_ANALYTICS_TRACKING_ID_SETTING_NAME, $domainId);
    }

    /**
     * @param string $code
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function replaceVariables($code, Order $order)
    {
        $variableReplacements = [
            self::VARIABLE_NUMBER => $order->getNumber(),
            self::VARIABLE_TOTAL_PRICE => $this->moneyExtension->moneyFormatFilter($order->getTotalPriceWithVat()),
        ];

        return strtr($code, $variableReplacements);
    }

    /**
     * @return string[]
     */
    public function getAllPagesBeforeContentScriptCodes(): array
    {
        return $this->getScriptCodesByPlacement(Script::PLACEMENT_ALL_PAGES);
    }

    /**
     * @return string[]
     */
    public function getAllPagesAfterContentScriptCodes(): array
    {
        return $this->getScriptCodesByPlacement(Script::PLACEMENT_ALL_PAGES_AFTER_CONTENT);
    }

    /**
     * @param string $placement
     * @return string[]
     */
    protected function getScriptCodesByPlacement(string $placement): array
    {
        $scripts = $this->scriptRepository->getScriptsByPlacement($placement);
        $scriptCodes = [];

        foreach ($scripts as $script) {
            $scriptCodes[] = $script->getCode();
        }

        return $scriptCodes;
    }

    /**
     * @return string[]
     */
    public function getAvailablePlacementChoices(): array
    {
        return [
            t('After content (all pages)') => Script::PLACEMENT_ALL_PAGES_AFTER_CONTENT,
            t('Order confirmation page') => Script::PLACEMENT_ORDER_SENT_PAGE,
            t('Before content (all pages)') => Script::PLACEMENT_ALL_PAGES,
        ];
    }
}
