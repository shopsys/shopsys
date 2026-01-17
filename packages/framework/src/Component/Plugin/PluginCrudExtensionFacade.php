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

    /**
     * @param string $type
     * @param string $name
     */
    public function extendForm(FormBuilderInterface $builder, $type, $name): void
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

    /**
     * @param string $type
     * @param int $id
     * @return array
     */
    public function getAllData($type, $id)
    {
        $allData = [];

        foreach ($this->pluginCrudExtensionRegistry->getCrudExtensions($type) as $key => $crudExtension) {
            $allData[$key] = $crudExtension->getData($id);
        }

        return $allData;
    }

    /**
     * @param string $type
     * @param int $id
     */
    public function saveAllData($type, $id, array $allData): void
    {
        foreach ($this->pluginCrudExtensionRegistry->getCrudExtensions($type) as $key => $crudExtension) {
            if (array_key_exists($key, $allData)) {
                $crudExtension->saveData($id, $allData[$key]);
            }
        }
    }

    /**
     * @param string $type
     * @param int $id
     */
    public function removeAllData($type, $id): void
    {
        foreach ($this->pluginCrudExtensionRegistry->getCrudExtensions($type) as $crudExtension) {
            $crudExtension->removeData($id);
        }
    }
}
