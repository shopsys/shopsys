<?php

declare(strict_types=1);

namespace App\Form\Type;

use FOS\CKEditorBundle\Config\CKEditorConfigurationInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class CKEditorTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \FOS\CKEditorBundle\Config\CKEditorConfigurationInterface
     */
    private $configuration;

    /**
     * @param \FOS\CKEditorBundle\Config\CKEditorConfigurationInterface $configuration
     */
    public function __construct(CKEditorConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['config']['allowedContent'] = true;
        $builder->setAttribute('config', $this->resolveConfig($options));
    }

    /**
     * @param array $options
     * @return array
     */
    private function resolveConfig(array $options): array
    {
        $config = $options['config'];

        if ($options['config_name'] === null) {
            $options['config_name'] = uniqid('fos', true);
        } else {
            $config = array_merge($this->configuration->getConfig($options['config_name']), $config);
        }

        if (isset($config['toolbar']) && is_string($config['toolbar'])) {
            $config['toolbar'] = $this->configuration->getToolbar($config['toolbar']);
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CKEditorType::class;
    }
}
