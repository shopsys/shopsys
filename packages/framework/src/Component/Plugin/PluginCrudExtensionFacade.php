<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Plugin;

use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\FormBuilderInterface;

class PluginCrudExtensionFacade
{
    public function __construct(protected readonly PluginCrudExtensionRegistry $pluginCrudExtensionRegistry)
    {
    }

    public function extendForm(FormBuilderInterface $builder, string $type, string $name): void
    {
        $crudExtensions = $this->pluginCrudExtensionRegistry->getCrudExtensions($type);

        foreach ($crudExtensions as $key => $crudExtension) {
            $builderExtensionGroup = $builder->create($key . 'Group', GroupType::class, [
                'label' => $crudExtension->getFormLabel(),
            ]);

            $builderExtensionGroup->add($key, $crudExtension->getFormTypeClass(), [
                'label' => false,
                'property_path' => sprintf('%s[%s]', $name, $key),
            ]);

            $builder->add($builderExtensionGroup);
        }
    }

    public function getAllData(string $type, int $id): array
    {
        $allData = [];

        foreach ($this->pluginCrudExtensionRegistry->getCrudExtensions($type) as $key => $crudExtension) {
            $allData[$key] = $crudExtension->getData($id);
        }

        return $allData;
    }

    public function saveAllData(string $type, int $id, array $allData): void
    {
        foreach ($this->pluginCrudExtensionRegistry->getCrudExtensions($type) as $key => $crudExtension) {
            if (array_key_exists($key, $allData)) {
                $crudExtension->saveData($id, $allData[$key]);
            }
        }
    }

    public function removeAllData(string $type, int $id): void
    {
        foreach ($this->pluginCrudExtensionRegistry->getCrudExtensions($type) as $crudExtension) {
            $crudExtension->removeData($id);
        }
    }
}
