<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

class <?= $class_name; ?>
{
<?php if ($entity_config->hasUuid): ?>
    public ?string $uuid;
<?php endif; ?>
<?php foreach ($entity_config->getAllProperties() as $property): ?>
    public <?= $property->isForTranslation() ? 'array' : $property->getTypeHint(); ?> $<?= $property->propertyName; ?>;
<?php endforeach; ?>
}
