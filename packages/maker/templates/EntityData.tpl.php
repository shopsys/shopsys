<?php

use Shopsys\MakerBundle\EntityConfig\CollectionTypeHintTypeEnum;

?>
<?= "<?php\n"; ?>
<?php /** @var \Shopsys\MakerBundle\EntityConfig\EntityConfig $entity_config */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

class <?= $class_name; ?>
{
<?php if ($entity_config->hasUuid): ?>
    public ?string $uuid;
<?php endif; ?>
<?php foreach ($entity_config->getAllProperties() as $property): ?>
    <?= $property->getAnnotationForDataObject(); ?>
    public <?= $property->isForTranslation() || $property->isForDomain() ? 'array' : $property->getTypeHint(CollectionTypeHintTypeEnum::ARRAY); ?> $<?= $property->propertyName; ?>;
<?php endforeach; ?>
}
