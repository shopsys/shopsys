<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\Setting\LuigisBoxFeedSettingEnum;

class LuigisBoxCrudExtension implements PluginCrudExtensionInterface
{
    public function __construct(
        protected readonly Setting $setting,
        protected readonly LuigisBoxFeedSettingEnum $luigisBoxFeedSettingEnum,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getFormTypeClass(): string
    {
        return LuigisBoxSettingFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getFormLabel(): string
    {
        return t('Luigi\'s Box settings');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getData($id): array
    {
        $data = [];

        foreach ($this->luigisBoxFeedSettingEnum->getAllCases() as $settingName) {
            $data[$settingName] = $this->setting->getForDomain($settingName, $this->adminDomainTabsFacade->getSelectedDomainId());
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function saveData($id, $data): void
    {
        foreach ($data as $name => $value) {
            $this->setting->setForDomain($name, $value, $this->adminDomainTabsFacade->getSelectedDomainId());
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function removeData($id): void
    {
        foreach ($this->luigisBoxFeedSettingEnum->getAllCases() as $settingName) {
            $this->setting->deleteByName($settingName);
        }
    }
}
