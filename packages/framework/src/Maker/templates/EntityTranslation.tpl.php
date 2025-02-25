<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[] $translation_properties */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

/**
 * @ORM\Table(name="<?= $entity_config->tableName . '_translations' ?>")
 * @ORM\Entity
 */
class <?= $class_name; ?> extends AbstractTranslation
{
    /**
     * @var \<?= $entity_config->getEntityFullyQualifiedName(); ?><?= PHP_EOL; ?>
     * @Prezent\Translatable(targetEntity="<?= $entity_config->getEntityFullyQualifiedName(); ?>")
     */
    protected $translatable;

<?php foreach ($translation_properties as $property): ?>
    /**<?= PHP_EOL; ?>
     * <?= implode(PHP_EOL . '     * ', $property->getAnnotationLines()) . PHP_EOL; ?>
     */<?= PHP_EOL; ?>
    private <?= $property->getTypeHint() ?> $<?= $property->propertyName; ?>;
    <?= PHP_EOL; ?>
<?php endforeach; ?>

<?php foreach ($translation_properties as $property): ?>
    public function <?= $property->getGetterName(); ?>(): <?= $property->getTypeHint(); ?>
    {
        return $this-><?= $property->propertyName; ?>;
    }
<?php endforeach; ?>

<?php foreach ($translation_properties as $property): ?>
    public function set<?= ucfirst($property->propertyName); ?>(<?= $property->getTypeHint() ?> $<?= $property->propertyName; ?>): void
    {
        $this-><?= $property->propertyName; ?> = $<?= $property->propertyName; ?>;
    }
<?php endforeach; ?>
}
