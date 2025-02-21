<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[] $entity_properties */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

class <?= $class_name; ?>
{
<?php if ($entity_config->hasUuid): ?>
    public ?string $uuid;
<?php endif; ?>
<?php foreach ($entity_properties as $property): ?>
    public <?= $property->getTypeHint(); ?> $<?= $property->propertyName; ?>;
<?php endforeach; ?>
}
