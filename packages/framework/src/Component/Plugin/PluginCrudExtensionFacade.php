<?php

namespace Shopsys\FrameworkBundle\Component\Plugin;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

class PluginCrudExtensionFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionRegistry
     */
    protected $pluginCrudExtensionRegistry;

    public function __construct(PluginCrudExtensionRegistry $pluginCrudExtensionRegistry)
    {
        $this->pluginCrudExtensionRegistry = $pluginCrudExtensionRegistry;
    }
    
    public function extendForm(FormBuilderInterface $builder, string $type, string $name): void
    {
        $builder->add($name, FormType::class, [
            'compound' => true,
        ]);

        foreach ($this->pluginCrudExtensionRegistry->getCrudExtensions($type) as $key => $crudExtension) {
            $builder->get($name)->add($key, $crudExtension->getFormTypeClass(), [
                'label' => $crudExtension->getFormLabel(),
            ]);
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
