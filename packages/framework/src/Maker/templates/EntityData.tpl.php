<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityFieldsConfiguration $entity_fields_configuration */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

class <?= $class_name; ?>
{
<?php if ($entity_config->hasUuid): ?>
    public ?string $uuid;
<?php endif; ?>
<?php foreach ($entity_fields_configuration->getAllProperties() as $property): ?>
    public <?= $property->isForTranslation() ? 'array' : $property->getTypeHint(); ?> $<?= $property->propertyName; ?>;
<?php endforeach; ?>
}
