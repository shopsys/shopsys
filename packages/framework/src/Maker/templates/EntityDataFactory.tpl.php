<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[] $entity_properties */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

class <?= $class_name; ?>
{
    public function create(): <?= $entity_config->entityName; ?>Data
    {
        $<?= lcfirst($entity_config->entityName); ?>Data = $this->createInstance();
        $this->fillNew($<?= lcfirst($entity_config->entityName); ?>Data);

        return $<?= lcfirst($entity_config->entityName); ?>Data;
    }

    public function createFrom<?= $entity_config->entityName; ?>(<?= $entity_config->entityName; ?> $<?= lcfirst($entity_config->entityName); ?>): <?= $entity_config->entityName; ?>Data
    {
        $<?= lcfirst($entity_config->entityName); ?>Data = $this->createInstance();

        <?php if ($entity_config->hasUuid): ?>
            $<?= lcfirst($entity_config->entityName); ?>Data->uuid = $<?= lcfirst($entity_config->entityName); ?>->getUuid();
        <?php endif; ?>
        <?php foreach ($entity_properties as $property): ?>
            $<?= lcfirst($entity_config->entityName); ?>Data-><?= $property->propertyName; ?> = $<?= lcfirst($entity_config->entityName); ?>-><?= $property->getGetterName(); ?>;
        <?php endforeach; ?>

        return $<?= lcfirst($entity_config->entityName); ?>Data;
    }

    private function createInstance(): <?= $entity_config->entityName; ?>Data
    {
        return new <?= $entity_config->entityName; ?>Data();
    }

    private function fillNew(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
        // TODO set default values here if necessary
    }
}
